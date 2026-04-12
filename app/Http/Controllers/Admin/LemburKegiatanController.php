<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LemburKegiatan;
use App\Models\LemburKegiatanPegawai;
use App\Models\AbsensiLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class LemburKegiatanController extends Controller
{
    // ─────────────────────────────────────────────────────────
    // INDEX — Daftar semua kegiatan lembur (filter by bulan)
    // ─────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $bulanParam = $request->input('bulan', Carbon::now()->format('Y-m'));
        $parsedDate = Carbon::createFromFormat('Y-m', $bulanParam);

        $kegiatan = LemburKegiatan::with(['dibuatOleh', 'pegawaiAssignments'])
            ->whereMonth('tanggal', $parsedDate->month)
            ->whereYear('tanggal', $parsedDate->year)
            ->orderBy('tanggal', 'asc')
            ->get();

        return view('admin.lembur-kegiatan.index', compact('kegiatan', 'bulanParam'));
    }

    // ─────────────────────────────────────────────────────────
    // CREATE FORM
    // ─────────────────────────────────────────────────────────
    public function create(Request $request)
    {
        // Pre-fill tanggal jika datang dari query string (misal dari tombol di rekap)
        $tanggalDefault = $request->input('tanggal', Carbon::now()->format('Y-m-d'));

        // Daftar pegawai (Pegawai + Dosen) untuk di-assign
        $pegawais = User::whereHas('roles', fn($q) => $q->whereIn('title', ['Pegawai', 'Dosen']))
            ->whereNotNull('nip')
            ->orderBy('name')
            ->get();

        return view('admin.lembur-kegiatan.create', compact('tanggalDefault', 'pegawais'));
    }

    // ─────────────────────────────────────────────────────────
    // STORE — Simpan kegiatan baru + assignment awal (opsional)
    // ─────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'tanggal'          => 'required|date',
            'nama_kegiatan'    => 'required|string|max:255',
            'deskripsi'        => 'nullable|string',
            'file_surat_tugas' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // max 5MB
            // pegawai_ids & perans bersifat opsional saat create
            'pegawai_ids'      => 'nullable|array',
            'pegawai_ids.*'    => 'exists:users,id',
            'perans'           => 'nullable|array',
        ]);

        DB::transaction(function () use ($request) {
            // Upload surat tugas jika ada
            $filePath = null;
            if ($request->hasFile('file_surat_tugas')) {
                $filePath = $request->file('file_surat_tugas')
                    ->store('lembur/surat-tugas', 'public');
            }

            $kegiatan = LemburKegiatan::create([
                'tanggal'          => $request->tanggal,
                'nama_kegiatan'    => $request->nama_kegiatan,
                'deskripsi'        => $request->deskripsi,
                'file_surat_tugas' => $filePath,
                'dibuat_oleh'      => Auth::id(),
            ]);

            // Assign pegawai jika ada yang dipilih
            if ($request->filled('pegawai_ids')) {
                $this->syncPegawai($kegiatan, $request->pegawai_ids, $request->perans ?? []);
            }
        });

        return redirect()->route('admin.lembur-kegiatan.index')
            ->with('message', 'Kegiatan lembur berhasil dibuat.');
    }

    // ─────────────────────────────────────────────────────────
    // SHOW — Detail kegiatan + status validasi per pegawai
    // ─────────────────────────────────────────────────────────
    public function show(LemburKegiatan $lemburKegiatan)
    {
        $lemburKegiatan->load(['dibuatOleh', 'pegawaiAssignments.user']);

        $tanggal = $lemburKegiatan->tanggal->format('Y-m-d');

        // Gabungkan data assignment dengan data presensi hari itu
        $assignments = $lemburKegiatan->pegawaiAssignments->map(function ($assignment) use ($tanggal) {
            $log = AbsensiLog::where('user_id', $assignment->user_id)
                ->whereDate('tanggal', $tanggal)
                ->first();

            $assignment->log        = $log;
            $assignment->durasi_menit = null;

            if ($log && $log->jam_masuk && $log->jam_keluar &&
                $log->jam_masuk !== '-' && $log->jam_keluar !== '-') {
                try {
                    $masuk  = Carbon::createFromFormat('H:i', $log->jam_masuk);
                    $keluar = Carbon::createFromFormat('H:i', $log->jam_keluar);
                    if ($keluar->gt($masuk)) {
                        $assignment->durasi_menit = $masuk->diffInMinutes($keluar);
                    }
                } catch (\Exception $e) { /* silent */ }
            }

            return $assignment;
        });

        return view('admin.lembur-kegiatan.show', compact('lemburKegiatan', 'assignments', 'tanggal'));
    }

    // ─────────────────────────────────────────────────────────
    // EDIT FORM
    // ─────────────────────────────────────────────────────────
    public function edit(LemburKegiatan $lemburKegiatan)
    {
        $lemburKegiatan->load('pegawaiAssignments.user');

        $pegawais = User::whereHas('roles', fn($q) => $q->whereIn('title', ['Pegawai', 'Dosen']))
            ->whereNotNull('nip')
            ->orderBy('name')
            ->get();

        // Pegawai yang sudah diassign saat ini
        $assignedIds = $lemburKegiatan->pegawaiAssignments->pluck('user_id')->toArray();
        $assignedPerans = $lemburKegiatan->pegawaiAssignments->pluck('peran', 'user_id')->toArray();

        return view('admin.lembur-kegiatan.edit', compact(
            'lemburKegiatan', 'pegawais', 'assignedIds', 'assignedPerans'
        ));
    }

    // ─────────────────────────────────────────────────────────
    // UPDATE — Update kegiatan & re-sync assignment
    // ─────────────────────────────────────────────────────────
    public function update(Request $request, LemburKegiatan $lemburKegiatan)
    {
        $request->validate([
            'tanggal'          => 'required|date',
            'nama_kegiatan'    => 'required|string|max:255',
            'deskripsi'        => 'nullable|string',
            'file_surat_tugas' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'pegawai_ids'      => 'nullable|array',
            'pegawai_ids.*'    => 'exists:users,id',
            'perans'           => 'nullable|array',
        ]);

        DB::transaction(function () use ($request, $lemburKegiatan) {
            $filePath = $lemburKegiatan->file_surat_tugas;
            if ($request->hasFile('file_surat_tugas')) {
                // Hapus file lama
                if ($filePath) Storage::disk('public')->delete($filePath);
                $filePath = $request->file('file_surat_tugas')
                    ->store('lembur/surat-tugas', 'public');
            }

            $lemburKegiatan->update([
                'tanggal'          => $request->tanggal,
                'nama_kegiatan'    => $request->nama_kegiatan,
                'deskripsi'        => $request->deskripsi,
                'file_surat_tugas' => $filePath,
            ]);

            // Re-sync pegawai: hapus semua yang lama, insert yang baru
            // (status_validasi akan di-reset ke 'menunggu' untuk entry baru,
            //  tapi kita pertahankan 'valid' untuk yang sudah valid agar tidak hilang)
            $existingValid = $lemburKegiatan->pegawaiAssignments()
                ->where('status_validasi', 'valid')
                ->pluck('user_id')
                ->toArray();

            $lemburKegiatan->pegawaiAssignments()->delete();

            if ($request->filled('pegawai_ids')) {
                $this->syncPegawai($kegiatan ?? $lemburKegiatan, $request->pegawai_ids, $request->perans ?? [], $existingValid);
            }
        });

        return redirect()->route('admin.lembur-kegiatan.show', $lemburKegiatan)
            ->with('message', 'Kegiatan berhasil diperbarui.');
    }

    // ─────────────────────────────────────────────────────────
    // DESTROY
    // ─────────────────────────────────────────────────────────
    public function destroy(LemburKegiatan $lemburKegiatan)
    {
        if ($lemburKegiatan->file_surat_tugas) {
            Storage::disk('public')->delete($lemburKegiatan->file_surat_tugas);
        }
        $lemburKegiatan->delete(); // cascade ke pivot via onDelete('cascade')

        return redirect()->route('admin.lembur-kegiatan.index')
            ->with('message', 'Kegiatan lembur berhasil dihapus.');
    }

    // ─────────────────────────────────────────────────────────
    // ASSIGN PEGAWAI — Endpoint tambah/hapus pegawai dari kegiatan
    // (dipakai via AJAX di halaman show, untuk assign setelah hari H)
    // ─────────────────────────────────────────────────────────
    public function assignPegawai(Request $request, LemburKegiatan $lemburKegiatan)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'peran'   => 'nullable|string|max:100',
        ]);

        $userId = $request->user_id;
        $tanggal = $lemburKegiatan->tanggal->format('Y-m-d');

        // Cek: apakah pegawai ini sudah diassign di kegiatan LAIN pada tanggal yang sama?
        $sudahDiKegiatan = LemburKegiatanPegawai::whereHas('kegiatan', function ($q) use ($tanggal) {
                $q->whereDate('tanggal', $tanggal);
            })
            ->where('user_id', $userId)
            ->where('lembur_kegiatan_id', '!=', $lemburKegiatan->id)
            ->exists();

        if ($sudahDiKegiatan) {
            return response()->json([
                'success' => false,
                'message' => 'Pegawai ini sudah diassign ke kegiatan lain di tanggal yang sama.'
            ], 422);
        }

        // Hitung status validasi langsung jika data presensi sudah ada
        $statusValidasi = $this->hitungStatusValidasi($userId, $tanggal);

        $assignment = LemburKegiatanPegawai::updateOrCreate(
            [
                'lembur_kegiatan_id' => $lemburKegiatan->id,
                'user_id'            => $userId,
            ],
            [
                'peran'            => $request->peran,
                'status_validasi'  => $statusValidasi,
            ]
        );

        $assignment->load('user');

        return response()->json([
            'success'    => true,
            'assignment' => $assignment,
            'message'    => 'Pegawai berhasil ditambahkan.'
        ]);
    }

    public function removePegawai(Request $request, LemburKegiatan $lemburKegiatan)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);

        LemburKegiatanPegawai::where('lembur_kegiatan_id', $lemburKegiatan->id)
            ->where('user_id', $request->user_id)
            ->delete();

        return response()->json(['success' => true, 'message' => 'Pegawai berhasil dihapus dari kegiatan.']);
    }

    // ─────────────────────────────────────────────────────────
    // REKAP KEUANGAN — View ringkas untuk keperluan pencairan
    // ─────────────────────────────────────────────────────────
    public function rekapKeuangan(Request $request)
    {
        $bulanParam = $request->input('bulan', Carbon::now()->format('Y-m'));
        $parsedDate = Carbon::createFromFormat('Y-m', $bulanParam);

        // Ambil semua assignment yang valid di bulan ini
        $rekap = LemburKegiatanPegawai::with(['user', 'kegiatan'])
            ->whereHas('kegiatan', function ($q) use ($parsedDate) {
                $q->whereMonth('tanggal', $parsedDate->month)
                  ->whereYear('tanggal', $parsedDate->year);
            })
            ->where('status_validasi', 'valid')
            ->get()
            ->groupBy('user_id'); // Group by pegawai

        // Hitung durasi presensi untuk setiap assignment yang valid
        $rekapDetail = $rekap->map(function ($assignments, $userId) {
            return $assignments->map(function ($assignment) {
                $tanggal = $assignment->kegiatan->tanggal->format('Y-m-d');
                $log = AbsensiLog::where('user_id', $assignment->user_id)
                    ->whereDate('tanggal', $tanggal)
                    ->first();

                $durasiJam = null;
                if ($log && $log->jam_masuk && $log->jam_keluar &&
                    $log->jam_masuk !== '-' && $log->jam_keluar !== '-') {
                    try {
                        $masuk  = Carbon::createFromFormat('H:i', $log->jam_masuk);
                        $keluar = Carbon::createFromFormat('H:i', $log->jam_keluar);
                        if ($keluar->gt($masuk)) {
                            $durasiJam = round($masuk->diffInMinutes($keluar) / 60, 2);
                        }
                    } catch (\Exception $e) { /* silent */ }
                }

                return [
                    'kegiatan'     => $assignment->kegiatan->nama_kegiatan,
                    'tanggal'      => $tanggal,
                    'peran'        => $assignment->peran,
                    'jam_masuk'    => $log->jam_masuk ?? '-',
                    'jam_keluar'   => $log->jam_keluar ?? '-',
                    'durasi_jam'   => $durasiJam,
                ];
            });
        });

        // Flatten untuk tampil per-baris di tabel
        $rows = LemburKegiatanPegawai::with(['user', 'kegiatan'])
            ->whereHas('kegiatan', function ($q) use ($parsedDate) {
                $q->whereMonth('tanggal', $parsedDate->month)
                  ->whereYear('tanggal', $parsedDate->year)
                  ->orderBy('tanggal');
            })
            ->where('status_validasi', 'valid')
            ->get()
            ->map(function ($assignment) {
                $tanggal = $assignment->kegiatan->tanggal->format('Y-m-d');
                $log = AbsensiLog::where('user_id', $assignment->user_id)
                    ->whereDate('tanggal', $tanggal)->first();

                $durasiMenit = null;
                $durasiJam   = null;
                if ($log && $log->jam_masuk && $log->jam_keluar &&
                    $log->jam_masuk !== '-' && $log->jam_keluar !== '-') {
                    try {
                        $masuk  = Carbon::createFromFormat('H:i', $log->jam_masuk);
                        $keluar = Carbon::createFromFormat('H:i', $log->jam_keluar);
                        if ($keluar->gt($masuk)) {
                            $durasiMenit = $masuk->diffInMinutes($keluar);
                            $durasiJam   = round($durasiMenit / 60, 2);
                        }
                    } catch (\Exception $e) { /* silent */ }
                }

                return (object)[
                    'nama'         => $assignment->user->name ?? '-',
                    'nip'          => $assignment->user->nip ?? '-',
                    'kegiatan'     => $assignment->kegiatan->nama_kegiatan,
                    'tanggal'      => $tanggal,
                    'peran'        => $assignment->peran ?? '-',
                    'jam_masuk'    => $log->jam_masuk ?? '-',
                    'jam_keluar'   => $log->jam_keluar ?? '-',
                    'durasi_jam'   => $durasiJam,
                    'surat_tugas'  => $assignment->kegiatan->file_surat_tugas,
                ];
            })
            ->sortBy(['tanggal', 'nama']);

        return view('admin.lembur-kegiatan.rekap-keuangan', compact('rows', 'bulanParam'));
    }

    // ─────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────

    /**
     * Hitung status validasi berdasarkan data presensi yang sudah ada di DB.
     * Dipanggil saat assign manual (setelah hari H) atau saat update.
     */
    private function hitungStatusValidasi(int $userId, string $tanggal): string
    {
        $sudahLewat = Carbon::parse($tanggal)->isPast() && !Carbon::parse($tanggal)->isToday();
        $log = AbsensiLog::where('user_id', $userId)->whereDate('tanggal', $tanggal)->first();

        // Tidak ada log / tidak ada scan masuk sama sekali
        if (!$log || !$log->jam_masuk || $log->jam_masuk === '-') {
            return $sudahLewat ? 'tidak_fr' : 'menunggu';
        }

        // Ada scan masuk tapi tidak ada scan pulang
        if (!$log->jam_keluar || $log->jam_keluar === '-') {
            return $sudahLewat ? 'tidak_valid' : 'menunggu';
        }

        // Ada keduanya → hitung durasi
        try {
            $masuk  = Carbon::createFromFormat('H:i', $log->jam_masuk);
            $keluar = Carbon::createFromFormat('H:i', $log->jam_keluar);
            if ($keluar->gt($masuk) && $masuk->diffInMinutes($keluar) >= 240) {
                return 'valid';
            }
        } catch (\Exception $e) { /* silent */ }

        // Ada scan keduanya tapi durasi < 4 jam
        return 'tidak_valid';
    }

    /**
     * Sync assignment pegawai ke kegiatan.
     * $existingValid: array user_id yang sudah valid sebelumnya (preserve status-nya).
     */
    private function syncPegawai(LemburKegiatan $kegiatan, array $pegawaiIds, array $perans, array $existingValid = []): void
    {
        $tanggal = $kegiatan->tanggal->format('Y-m-d');

        foreach ($pegawaiIds as $userId) {
            // Skip jika sudah diassign di kegiatan lain di tanggal yang sama
            $sudahDiKegiatan = LemburKegiatanPegawai::whereHas('kegiatan', function ($q) use ($tanggal) {
                    $q->whereDate('tanggal', $tanggal);
                })
                ->where('user_id', $userId)
                ->where('lembur_kegiatan_id', '!=', $kegiatan->id)
                ->exists();

            if ($sudahDiKegiatan) continue;

            // Pertahankan 'valid' jika sebelumnya sudah valid
            $statusValidasi = in_array($userId, $existingValid)
                ? 'valid'
                : $this->hitungStatusValidasi($userId, $tanggal);

            LemburKegiatanPegawai::create([
                'lembur_kegiatan_id' => $kegiatan->id,
                'user_id'            => $userId,
                'peran'              => $perans[$userId] ?? null,
                'status_validasi'    => $statusValidasi,
            ]);
        }
    }
}