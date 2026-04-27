<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyKegiatanRequest;
use App\Http\Requests\StoreKegiatanRequest;
use App\Http\Requests\UpdateKegiatanRequest;
use App\Services\EventService;
use App\Models\Kegiatan;
use App\Models\Ruangan;
use App\Models\User;
use App\Models\JadwalPerkuliahan;
use App\Models\Barang;
use App\Imports\KegiatanImport;
use App\Exports\KegiatanTemplateExport;
use Maatwebsite\Excel\Facades\Excel;
use Gate;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;
use App\Services\TelegramService;

class KegiatanController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('kegiatan_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if ($request->ajax()) {

            $query = Kegiatan::with(['ruangan', 'user'])
                ->withCount(['barangs as barangs_dipinjam_count' => function ($q) {
                    $q->where('barang_kegiatan.status', 'dipinjam');
                }])

                ->addSelect(sprintf('%s.*', (new Kegiatan())->table));


            if ($request->filled('tanggal_mulai')) {
            $query->whereDate('waktu_mulai', '=', $request->tanggal_mulai);
            }
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }
            if ($request->filled('ruangan_id')) {
                $query->where('ruangan_id', $request->ruangan_id);
            }

            // Filter hanya jika user punya role Pegawai/User DAN BUKAN Admin
            if (!auth()->user()->isAdmin() && (auth()->user()->hasRole('User') || auth()->user()->hasRole('Pegawai'))) {
                $query->where($tableName . '.user_id', auth()->id());
            }

            $table = Datatables::of($query);

            if (empty($request->input('order'))) {
                $table->order(function ($query) {
                    $query->orderBy('created_at', 'desc');
                });
            }

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');
            $table->addColumn('persetujuan', '&nbsp;');
            $table->addColumn('pinjam_barang', function ($row) {
                if ($row->barangs_dipinjam_count > 0) {
                    return '<span class="badge-status badge-pinjam-ya">Pinjam ('.$row->barangs_dipinjam_count.')</span>';
                }

                return '<span class="badge-status badge-pinjam-tidak">Tidak</span>';
            });


            $table->editColumn('actions', function ($row) {
                $user = auth()->user();
                
                // Buka div btn-group
                $buttons = '<div class="btn-group shadow-sm">';

                if ($user->can('kegiatan_show')) {
                    $buttons .= '<a class="btn btn-sm btn-info" href="' . route('admin.kegiatan.show', $row->id) . '" title="Detail"><i class="fas fa-eye text-white"></i></a>';
                }

                $allowEditDelete = true;

                if ($user->hasRole('User')) {
                    $editableStatuses = [
                        'belum_disetujui',          
                        'revisi_operator',          
                        'revisi_kemahasiswaan',
                        'revisi_kasubag_akademik',
                        'revisi_kasubag_sarpras'
                    ];

                    // Jika status saat ini TIDAK ada di daftar editable, maka kunci tombol
                    if (!in_array($row->status, $editableStatuses)) {
                        $allowEditDelete = false;
                    }
                }

                if ($allowEditDelete) {
                    if ($user->can('kegiatan_edit')) {
                        $buttons .= '<a class="btn btn-sm btn-success" href="' . route('admin.kegiatan.edit', $row->id) . '" title="Edit"><i class="fas fa-edit text-white"></i></a>';
                    }
                    if ($user->can('kegiatan_delete')) {
                        // Cek apakah data ini punya recurring_group_id (true/false)
                        $isRecurring = $row->recurring_group_id ? 'true' : 'false';
                        
                        // Tambahkan data-is-recurring ke dalam tombol
                        $buttons .= '<button type="button" class="btn btn-sm btn-danger js-delete-btn" data-url="' . route('admin.kegiatan.destroy', $row->id) . '" data-is-recurring="' . $isRecurring . '" title="Hapus"><i class="fas fa-trash text-white"></i></button>';
                    }
                }

                // Tutup div btn-group
                $buttons .= '</div>';

                return $buttons;
            });

            $table->editColumn('waktu_mulai_formatted', function ($row) {
                return \Carbon\Carbon::parse($row->waktu_mulai)->translatedFormat('d M Y, H:i');
            });

            $table->editColumn('waktu_selesai_formatted', function ($row) {
                return \Carbon\Carbon::parse($row->waktu_selesai)->translatedFormat('d M Y, H:i');
            });

            $table->editColumn('created_at_human', function ($row) {
                return $row->created_at->diffForHumans();
            });

            $table->editColumn('created_at_title', function ($row) {
                return $row->created_at->format('d M Y, H:i:s');
            });
            
            $table->editColumn('persetujuan', function($row) {
                // Tampilkan kolom persetujuan jika user memiliki salah satu izin yang relevan
                if (!auth()->user()->can('persetujuan_access') && !auth()->user()->can('kegiatan_edit_status')) {
                    return '-';
                }

                // Di dalam $table->editColumn('persetujuan', function($row) { ...
                switch ($row->status) {
                    case 'belum_disetujui':
                        // Tombol untuk lanjut ke Kemahasiswaan
                        // Pastikan permission 'verifikasi_awal' atau role Operator dimiliki
                        return '<button type="button" class="btn btn-primary btn-sm js-open-modal" data-action-type="ajukan_ke_kemahasiswaan" data-id="'.$row->id.'">Ajukan ke Kemahasiswaan</button>';
                    
                    case 'verifikasi_kemahasiswaan':
                        // Tombol aksi untuk staff Kemahasiswaan
                        // Permission check: auth()->user()->can('verifikasi_kemahasiswaan')
                        return '<button class="btn btn-primary btn-sm js-open-modal" data-action-type="verifikasi_kemahasiswaan" data-id="'.$row->id.'">Verifikasi (Kemahasiswaan)</button>';

                    case 'verifikasi_kasubag_akademik':
                        // Tombol aksi untuk Kasubag Akademik
                        return '<button class="btn btn-primary btn-sm js-open-modal" data-action-type="verifikasi_kasubag_akademik" data-id="'.$row->id.'">Verifikasi (Akademik)</button>';

                    case 'verifikasi_kasubag_sarpras':
                        // Sesuai request: "Disetujui (operator yg aksi)"
                        // Berarti di tahap ini, tombol "Setujui" muncul.
                        // PERHATIAN: Siapa yang boleh klik? Jika Operator, pastikan permission-nya cek Operator.
                        // Jika Kasubag Sarpras yang klik, permissionnya cek Sarpras.
                        
                        $btnSetujui = '<button class="btn btn-success btn-sm js-open-modal" data-action-type="setujui" data-id="'.$row->id.'">Finalisasi (Disetujui)</button>';
                        $btnTolak = '<button class="btn btn-danger btn-sm js-open-modal ms-1" data-action-type="tolak" data-id="'.$row->id.'">Tolak</button>';
                        return $btnSetujui . $btnTolak;

                    case 'disetujui':
                        // Cek apakah kegiatan ini 'Bypass' (Dibuat Admin)
                        // Ciri-cirinya: Status disetujui, TAPI tidak punya timestamp verifikasi dari flow normal
                        // Pastikan nama kolom timestamp sesuai dengan yang ada di tabel kamu (misal: verifikasi_kemahasiswaan_at)
                        
                        if (
                            is_null($row->verifikasi_kemahasiswaan_at) && // Kolom Baru
                            is_null($row->verifikasi_akademik_at) &&      // Kolom Lama (Mapping: Kasubag Akademik)
                            is_null($row->verifikasi_sarpras_at)        // Kolom Lama (Mapping: Kasubag Sarpras)                // Tapi punya tanggal disetujui
                        ) {
                            return '<span class="text-muted fst-italic"><i class="fas fa-user-shield me-1"></i> Dibuat oleh Admin</span>';
                        }

                        // Jika lewat jalur normal (punya history verifikasi), tampilkan badge sukses biasa
                        return '<span class="badge-status badge-status-success">Telah Disetujui</span>';
                        
                    default:
                        // Handle status revisi / ditolak
                        if (Str::startsWith($row->status, 'revisi_')) {
                            return '<span class="badge-status badge-status-pending">Menunggu Revisi</span>';
                        }
                        return '-';
                }
            });

            // Di rawColumns, tambahkan 'persetujuan' dan 'placeholder'
            $table->rawColumns(['actions', 'placeholder', 'persetujuan', 'pinjam_barang']);

            // Mengembalikan data dalam format JSON
            return $table->make(true);
        }

        // Ambil data untuk dropdown filter (logika lama Anda dipertahankan)
        $users = User::pluck('name', 'id')->prepend('Semua Peminjam', '');
        $ruangans = Ruangan::pluck('nama', 'id')->prepend('Semua Ruangan', '');

        // Kita tidak mengirim 'kegiatan' lagi, karena akan diambil via AJAX
        return view('admin.kegiatan.index', compact('users', 'ruangans'));
    }

    public function create(Request $request)
    {
        abort_if(Gate::denies('kegiatan_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $ruangan = Ruangan::pluck('nama', 'id')->prepend(trans('global.pleaseSelect'), '');
        $users = User::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        $prefilledData = [];
        if ($request->has('permintaan_id')) {
            $permintaan = \App\Models\PermintaanKegiatan::with(['user', 'picUser'])->findOrFail($request->permintaan_id);
            
            $format = config('panel.date_format') . ' ' . config('panel.time_format');
            
            // --- PERBAIKAN DISINI ---
            // Kita paksa ubah string tanggal jadi Carbon dulu biar gak error
            $tanggal = \Carbon\Carbon::parse($permintaan->tanggal_kegiatan)->format('Y-m-d');
            
            $start = $tanggal . ' ' . \Carbon\Carbon::parse($permintaan->waktu_mulai)->format('H:i:s');
            $end = $tanggal . ' ' . \Carbon\Carbon::parse($permintaan->waktu_selesai)->format('H:i:s');
            // ------------------------

            $prefilledData = [
                'permintaan_id' => $permintaan->id,
                'nama_kegiatan' => $permintaan->nama_kegiatan,
                'jenis_kegiatan' => $permintaan->jenis_kegiatan,
                'waktu_mulai' => \Carbon\Carbon::parse($start)->format('Y-m-d H:i'),
                'waktu_selesai' => \Carbon\Carbon::parse($end)->format('Y-m-d H:i'),
                'deskripsi' => $permintaan->catatan_konsumsi, 
                'user_id' => $permintaan->user_id, 
                'nama_pic' => $permintaan->user->name ?? $permintaan->picUser->name,
                'nomor_telepon' => $permintaan->user->nomor_telepon ?? '', 
            ];
        }

        return view('admin.kegiatan.create', compact('ruangan', 'users', 'prefilledData'));
    }

    public function store(StoreKegiatanRequest $request, EventService $eventService)
    {
        $data = $request->all();
        $data['waktu_mulai'] = \Carbon\Carbon::parse($data['waktu_mulai'])->format('Y-m-d H:i:s');
        $data['waktu_selesai'] = \Carbon\Carbon::parse($data['waktu_selesai'])->format('Y-m-d H:i:s');
        
        if (!empty($data['berulang_sampai'])) {
            $data['berulang_sampai'] = \Carbon\Carbon::parse($data['berulang_sampai'])->format('Y-m-d');
        }
        // Pengecekan bentrok tetap sama
        $kegiatanBentrok = $eventService->isRoomTaken($request->all());

        if ($kegiatanBentrok) {
            // Ambil kapasitas ruangan yang diminta
            $ruanganDiminta = \App\Models\Ruangan::find($request->ruangan_id);
            $minKapasitas = $ruanganDiminta->kapasitas ?? 0;

            // Cari saran ruangan kosong
            $saranRuangan = $eventService->getSuggestedRooms($data, $minKapasitas);

            return redirect()->back()
                ->withInput($request->input())
                ->with('bentrok_kegiatan', $kegiatanBentrok->nama_kegiatan)
                ->with('saran_ruangan', $saranRuangan)
                ->withErrors('Ruangan ini tidak tersedia, karena bentrok dengan kegiatan: ' . $kegiatanBentrok->nama_kegiatan);
        }
    
        // PERUBAHAN UTAMA DI SINI
        $data = $request->all();

        if ($request->hasFile('poster')) {
            $data['poster'] = $request->file('poster')->store('posters', 'public');
        }
        
        // Logika status dan user_id tetap sama
        if (auth()->user()->hasRole('User')) {
            $data['status'] = 'belum_disetujui';
        } elseif (auth()->user()->hasRole('Admin')) {
            $data['status'] = 'disetujui';
        }
        
        // Panggil method yang membuat event(s) dan kembalikan model yang dibuat
        $created = $eventService->createEvents($data);
        
        if ($request->filled('permintaan_id') && !empty($created)) {
            // Ambil ID Permintaan
            $permintaan = \App\Models\PermintaanKegiatan::find($request->permintaan_id);
            
            if ($permintaan) {
                // Ambil kegiatan pertama yang baru dibuat sebagai referensi
                // ($created biasanya array karena support recurring events)
                $kegiatanUtama = is_array($created) ? $created[0] : $created;

                // 1. Update Status Ruang jadi SELESAI & Link ke Kegiatan ID
                $permintaan->update([
                    'status_ruang' => 'selesai',
                    'kegiatan_id'  => $kegiatanUtama->id,
                ]);

                // 2. Cek apakah status GLOBAL permintaan bisa diselesaikan?
                // Syarat: Status Konsumsi harus 'selesai' atau 'tidak_perlu'
                $konsumsiOk = in_array($permintaan->status_konsumsi, ['selesai', 'tidak_perlu']);
                
                if ($konsumsiOk) {
                    $permintaan->update(['status_permintaan' => 'selesai']);
                } else {
                    // Jika konsumsi belum beres, set status jadi 'proses'
                    $permintaan->update(['status_permintaan' => 'proses']);
                }
            }
        }
        // Buat history untuk setiap kegiatan yang baru dibuat
        if (!empty($created)) {
            foreach ($created as $model) {
                \App\Models\KegiatanHistory::create([
                    'kegiatan_id' => $model->id,
                    // actor = current authenticated user (may be admin)
                    'user_id' => auth()->id(),
                    'action' => 'created',
                    'note' => null,
                    // save borrower in meta so we know who the peminjam is
                    'meta' => json_encode(['borrower_id' => $model->user_id]),
                    'created_at' => $model->created_at,
                ]);
            }
        }

        return redirect()->route('admin.kegiatan.index')->with('success', 'Kegiatan berhasil disimpan!');
    }

    public function edit(Kegiatan $kegiatan)
    {
        abort_if(Gate::denies('kegiatan_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Tambahan: User tidak bisa edit jika sudah disetujui
        if (auth()->user()->hasRole('User') && $kegiatan->status === 'disetujui') {
            abort(Response::HTTP_FORBIDDEN, '403 Forbidden: Anda tidak dapat mengubah data yang sudah disetujui.');
        }

        $ruangan = Ruangan::pluck('nama', 'id')->prepend(trans('global.pleaseSelect'), '');

        $users = User::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        $kegiatan->load('ruangan', 'user');

        return view('admin.kegiatan.edit', compact('kegiatan', 'ruangan', 'users'));
    }

    public function update(UpdateKegiatanRequest $request, Kegiatan $kegiatan, EventService $eventService)
    {
        if (auth()->user()->hasRole('User') && $kegiatan->status === 'disetujui') {
            abort(Response::HTTP_FORBIDDEN, '403 Forbidden: Anda tidak dapat mengubah data yang sudah disetujui.');
        }

        // 1. Persiapan Data & Hitung Selisih Waktu (Offset)
        $data = $request->all();
        $rawOldStart = \Carbon\Carbon::parse($kegiatan->getRawOriginal('waktu_mulai'));
        $newStart = \Carbon\Carbon::parse($data['waktu_mulai']);
        $newEnd = \Carbon\Carbon::parse($data['waktu_selesai']);

        // Hitung selisih detik antara waktu lama dan baru untuk digeser ke acara lain
        $diffStartInSeconds = $rawOldStart->diffInSeconds($newStart, false);
        $diffDurationInSeconds = $newStart->diffInSeconds($newEnd, false);

        // Format untuk update record utama
        $data['waktu_mulai'] = $newStart->format('Y-m-d H:i:s');
        $data['waktu_selesai'] = $newEnd->format('Y-m-d H:i:s');

        // 2. Cek Bentrok untuk Acara Utama
        $payload = array_merge($kegiatan->toArray(), $data);
        $payload['ignore_id'] = $kegiatan->id;
        $bentrok = $eventService->isRoomTaken($payload);
        
        if ($bentrok) {
            $ruanganDiminta = \App\Models\Ruangan::find($request->ruangan_id);
            $minKapasitas = $ruanganDiminta->kapasitas ?? 0;
            $saranRuangan = $eventService->getSuggestedRooms($payload, $minKapasitas);

            return back()->withInput($request->all())
                ->with('bentrok_kegiatan', $bentrok->nama_kegiatan)
                ->with('saran_ruangan', $saranRuangan)
                ->withErrors('Bentrok dengan kegiatan: ' . $bentrok->nama_kegiatan);
        }

        // 3. Handle File Upload
        if ($request->hasFile('poster')) {
            if ($kegiatan->poster && \Storage::disk('public')->exists($kegiatan->poster)) {
                \Storage::disk('public')->delete($kegiatan->poster);
            }
            $data['poster'] = $request->file('poster')->store('posters', 'public');
        }
        if ($request->hasFile('surat_izin')) {
            if ($kegiatan->surat_izin && \Storage::disk('public')->exists($kegiatan->surat_izin)) {
                \Storage::disk('public')->delete($kegiatan->surat_izin);
            }
            $data['surat_izin'] = $request->file('surat_izin')->store('surat_izin', 'public');
        }

        // 4. LOGIKA UPDATE MASSAL (Termasuk Jam & Ruangan)
        $mode = $request->input('edit_mode', 'this');
        
        if ($kegiatan->recurring_group_id && $mode !== 'this') {
            // 1. Ambil semua event dalam rangkaian (kecuali yang sedang diedit)
            $query = Kegiatan::where('recurring_group_id', $kegiatan->recurring_group_id)
                            ->where('id', '!=', $kegiatan->id);

            if ($mode === 'following') {
                $query->where('waktu_mulai', '>=', $kegiatan->getRawOriginal('waktu_mulai'));
            }

            $affectedEvents = $query->get();

            // 2. TAHAP VALIDASI: Cek bentrok untuk SETIAP event baru
            foreach ($affectedEvents as $event) {
                $eventOldStart = \Carbon\Carbon::parse($event->getRawOriginal('waktu_mulai'));
                $eventNewStart = $eventOldStart->addSeconds($diffStartInSeconds);
                $eventNewEnd = $eventNewStart->copy()->addSeconds($diffDurationInSeconds);

                // Buat payload dummy untuk pengecekan
                $checkPayload = [
                    'ignore_id' => $event->id,
                    'ruangan_id' => $data['ruangan_id'],
                    'waktu_mulai' => $eventNewStart->format('Y-m-d H:i:s'),
                    'waktu_selesai' => $eventNewEnd->format('Y-m-d H:i:s'),
                ];

                // Panggil EventService
                $bentrokInstance = $eventService->isRoomTaken($checkPayload);

                if ($bentrokInstance) {
                    // Jika ada satu saja yang bentrok, batalkan semua dan kasih tahu tanggalnya
                    $tanggalBentrok = $eventNewStart->translatedFormat('d M Y');
                    return back()->withInput($request->all())
                        ->withErrors("Gagal update serentak! Terdapat bentrok pada tanggal $tanggalBentrok dengan kegiatan: " . $bentrokInstance->nama_kegiatan);
                }
            }

            foreach ($affectedEvents as $event) {
                // Hitung waktu baru berdasarkan offset
                $eventOldStart = \Carbon\Carbon::parse($event->getRawOriginal('waktu_mulai'));
                $eventNewStart = $eventOldStart->addSeconds($diffStartInSeconds);
                $eventNewEnd = $eventNewStart->copy()->addSeconds($diffDurationInSeconds);

                // Update detail + jam + ruangan
                $event->update([
                    'nama_kegiatan'      => $data['nama_kegiatan'],
                    'jenis_kegiatan'     => $data['jenis_kegiatan'],
                    'ruangan_id'         => $data['ruangan_id'],
                    'waktu_mulai'        => $eventNewStart->format('Y-m-d H:i:s'),
                    'waktu_selesai'      => $eventNewEnd->format('Y-m-d H:i:s'),
                    'nama_pic'           => $data['nama_pic'] ?? $event->nama_pic,
                    'nomor_telepon'      => $data['nomor_telepon'] ?? $event->nomor_telepon,
                    'deskripsi'          => $data['deskripsi'] ?? $event->deskripsi,
                    'poster'             => $data['poster'] ?? $event->poster,
                    'surat_izin'         => $data['surat_izin'] ?? $event->surat_izin,
                    'dosen_pembimbing_1' => $data['dosen_pembimbing_1'] ?? $event->dosen_pembimbing_1,
                    'dosen_pembimbing_2' => $data['dosen_pembimbing_2'] ?? $event->dosen_pembimbing_2,
                    'dosen_penguji_1'    => $data['dosen_penguji_1'] ?? $event->dosen_penguji_1,
                    'dosen_penguji_2'    => $data['dosen_penguji_2'] ?? $event->dosen_penguji_2,
                ]);
            }
        }

        // 5. Update Record Utama & History
        $oldStatus = $kegiatan->status;
        if (\Illuminate\Support\Str::startsWith($oldStatus, 'revisi_')) {
            $data['status'] = match($oldStatus) {
                'revisi_operator'          => 'belum_disetujui',
                'revisi_kemahasiswaan'     => 'verifikasi_kemahasiswaan',
                'revisi_kasubag_akademik'  => 'verifikasi_kasubag_akademik',
                'revisi_kasubag_sarpras'   => 'verifikasi_kasubag_sarpras',
                default                    => 'belum_disetujui',
            };
        }

        $kegiatan->update($data);

        \App\Models\KegiatanHistory::create([
            'kegiatan_id' => $kegiatan->id,
            'user_id' => auth()->id(),
            'action' => 'edited',
            'note' => "Update massal mode: $mode",
            'created_at' => now(),
        ]);

        return redirect()->route('admin.kegiatan.index')->with('success', 'Rangkaian kegiatan berhasil diperbarui.');
    }

    public function editSuratIzin(Kegiatan $kegiatan)
    {
        abort_if(Gate::denies('kegiatan_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.kegiatan.edit-surat-izin', compact('kegiatan'));
    }

    public function updateSuratIzin(Request $request, Kegiatan $kegiatan)
    {
    // Validasi file baru
    $request->validate([
        'surat_izin' => 'required|file|mimes:pdf|max:2048', // Pastikan file adalah PDF dan ukurannya di bawah 2MB
    ]);

    // Hapus file surat izin lama jika ada
    if ($kegiatan->surat_izin && \Storage::disk('public')->exists($kegiatan->surat_izin)) {
        \Storage::disk('public')->delete($kegiatan->surat_izin);
    }

    // Upload file surat izin baru
    $suratIzinPath = $request->file('surat_izin')->store('surat_izin', 'public');

    // Perbarui kolom surat_izin di database
    $kegiatan->update(['surat_izin' => $suratIzinPath]);

    return redirect()->route('admin.kegiatan.index')->with('success', 'Surat izin berhasil diperbarui.');
}

    public function show(Kegiatan $kegiatan)
    {
        abort_if(Gate::denies('kegiatan_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $kegiatan->load('ruangan', 'user', 'barangs');

        $barangs = \App\Models\Barang::where('stok', '>', 0)->get();

        return view('admin.kegiatan.show', compact('kegiatan', 'barangs'));
    }

    public function destroy(Request $request, Kegiatan $kegiatan)
    {
        abort_if(Gate::denies('kegiatan_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Tambahan: User tidak bisa hapus jika sudah disetujui
        if (auth()->user()->hasRole('User') && $kegiatan->status === 'disetujui') {
            abort(Response::HTTP_FORBIDDEN, '403 Forbidden: Anda tidak dapat menghapus data yang sudah disetujui.');
        }

        // Tangkap mode delete dari frontend (default 'this' jika tidak ada)
        $mode = $request->input('delete_mode', 'this'); 

        if ($kegiatan->recurring_group_id) {
            if ($mode === 'all') {
                Kegiatan::where('recurring_group_id', $kegiatan->recurring_group_id)->delete();
            } elseif ($mode === 'following') {
                // Ambil format asli dari database (Y-m-d H:i:s)
                $waktuMulaiMentah = $kegiatan->getRawOriginal('waktu_mulai');
                
                Kegiatan::where('recurring_group_id', $kegiatan->recurring_group_id)
                        ->where('waktu_mulai', '>=', $waktuMulaiMentah)
                        ->delete();
            } else {
                $kegiatan->delete();
            }
        } else {
            $kegiatan->delete();
        }

        return back();
    }

    public function massDestroy(MassDestroyKegiatanRequest $request)
    {
        $kegiatan = Kegiatan::find(request('ids'));

        foreach ($kegiatan as $kegiatan) {
            $kegiatan->delete();
        }

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function import(Request $request, EventService $eventService)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        try {
            $import = new KegiatanImport($eventService);
            Excel::import($import, $request->file('file'));

            $errors = $import->getRowErrors();

            if (!empty($errors)) {
                // Bungkus error ke dalam div ber-scroll untuk SweetAlert
                $errorHtml = '<div style="max-height: 250px; overflow-y: auto; text-align: left; background: #f8f9fa; padding: 10px; border-radius: 5px;">';
                $errorHtml .= '<ul style="margin-bottom: 0; padding-left: 20px; font-size: 14px; color: #dc3545;">';
                foreach ($errors as $err) {
                    $errorHtml .= "<li>{$err}</li>";
                }
                $errorHtml .= '</ul></div>';

                // Kita pakai session 'import_warning' khusus untuk mentrigger SweetAlert
                return redirect()->route('admin.kegiatan.index')
                    ->with('import_warning', $errorHtml);
            }

            return redirect()->route('admin.kegiatan.index')
                ->with('success', 'Semua data berhasil diimport tanpa ada masalah!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(new KegiatanTemplateExport, 'template_import_kegiatan.xlsx');
    }

    public function updateStatus(Request $request, Kegiatan $kegiatan, TelegramService $telegram)
    {
    // Validasi input
    $validated = $request->validate([
        'action' => 'required|in:next,back,reject,revise', // Aksi yang harus dilakukan
        'notes' => 'nullable|string',              // Validasi notes
    ]);

    // Logika perubahan status berdasarkan aksi
    $newStatus = $kegiatan->status;
    $successMessage = ''; // Variabel untuk pesan sukses

    switch ($validated['action']) {
        case 'next':
            // ALUR MAJU: Estafet ke tahap berikutnya
            switch ($kegiatan->status) {
                case 'belum_disetujui':
                    // Aksi Awal: Masuk ke Kemahasiswaan
                    $newStatus = 'verifikasi_kemahasiswaan';
                    $successMessage = 'Berhasil diajukan. Menunggu verifikasi Kemahasiswaan.';
                    break;
                case 'verifikasi_kemahasiswaan':
                    // Kemahasiswaan OK -> Lanjut Kasubag Akademik
                    $newStatus = 'verifikasi_kasubag_akademik';
                    $kegiatan->verifikasi_kemahasiswaan_at = now(); // Pastikan kolom ini ada di DB
                    $successMessage = 'Verifikasi Kemahasiswaan berhasil. Lanjut ke Kasubag Akademik.';
                    break;
                case 'verifikasi_kasubag_akademik':
                    // Akademik OK -> Lanjut Kasubag Sarpras
                    $newStatus = 'verifikasi_kasubag_sarpras';
                    $kegiatan->verifikasi_akademik_at = now();
                    $successMessage = 'Verifikasi Akademik berhasil. Lanjut ke Kasubag Sarpras.';
                    break;
                case 'verifikasi_kasubag_sarpras':
                    // Sarpras OK -> FINAL (Disetujui oleh Operator)
                    // Sesuai request: "Disetujui (operator yg aksi)"
                    // Ini mengasumsikan tombol diklik saat status masih di sarpras untuk mem-finalisasi
                    $newStatus = 'disetujui';
                    $kegiatan->verifikasi_sarpras_at = now();
                    $kegiatan->disetujui_at = now();
                    $successMessage = 'Kegiatan telah disetujui sepenuhnya dan diterbitkan!';
                    break;
            }
            break;

        case 'back':
            // ALUR MUNDUR (Opsional: jika verifikator ingin mengembalikan ke tahap sebelumnya tanpa revisi user)
            switch ($kegiatan->status) {
                case 'verifikasi_kemahasiswaan':
                    $newStatus = 'belum_disetujui';
                    break;
                case 'verifikasi_kasubag_akademik':
                    $newStatus = 'verifikasi_kemahasiswaan';
                    $kegiatan->verifikasi_kemahasiswaan_at = null;
                    break;
                case 'verifikasi_kasubag_sarpras':
                    $newStatus = 'verifikasi_kasubag_akademik';
                    $kegiatan->verifikasi_akademik_at = null;
                    break;
            }
            $successMessage = 'Status dikembalikan ke tahap sebelumnya.';
            break;

        case 'reject':
            $newStatus = 'ditolak';
            $kegiatan->ditolak_at = now();
            $successMessage = 'Kegiatan ditolak.';
            break;

        case 'revise':
            // ALUR REVISI: Mengembalikan ke pemohon dengan tag revisi spesifik
            switch ($kegiatan->status) {
                case 'verifikasi_kemahasiswaan':
                    $newStatus = 'revisi_kemahasiswaan';
                    break;
                case 'verifikasi_kasubag_akademik':
                    $newStatus = 'revisi_kasubag_akademik';
                    break;
                case 'verifikasi_kasubag_sarpras':
                    $newStatus = 'revisi_kasubag_sarpras';
                    break;
                default:
                    $newStatus = 'revisi_operator';
            }
            
            if (empty($validated['notes'])) {
                return redirect()->back()->with('error', 'Catatan revisi wajib diisi.');
            }

            // Logic simpan revisi (sama seperti kodemu)
            $kegiatan->revisi_by = auth()->id();
            $kegiatan->revisi_at = now();
            $kegiatan->revisi_level = str_replace('revisi_', '', $newStatus);
            $kegiatan->revisi_notes = $validated['notes'];
            $kegiatan->notes = $validated['notes'];
            $successMessage = 'Permintaan revisi dikirim ke pemohon.';
            break;

        default:
            return redirect()->back()->with('error', 'Aksi tidak valid!');
    }

    // Simpan perubahan status dan notes
    $kegiatan->status = $newStatus;
    if (!empty($validated['notes']) && $validated['action'] !== 'revise') {
        $kegiatan->notes = $validated['notes'];
    }

    // Jika bukan revisi, hapus data revisi lama (sudah ditangani)
    if ($validated['action'] !== 'revise') {
        $kegiatan->revisi_by = null;
        $kegiatan->revisi_at = null;
        $kegiatan->revisi_level = null;
        $kegiatan->revisi_notes = null;
    }
    $kegiatan->save();

    if ($kegiatan->user && $kegiatan->user->telegram_chat_id) {
        
        $icon = '';
        $pesanStatus = '';

        switch ($newStatus) {
            case 'verifikasi_kemahasiswaan':
                // Biasanya ini dari draft -> diajukan
                $icon = '📤';
                $pesanStatus = "Permohonan kamu <b>berhasil diajukan</b>. Sekarang menunggu verifikasi Kemahasiswaan.";
                break;
            case 'verifikasi_kasubag_akademik':
                $icon = '✅';
                $pesanStatus = "Lolos Kemahasiswaan! Sekarang menunggu verifikasi <b>Kasubag Akademik</b>.";
                break;
            case 'verifikasi_kasubag_sarpras':
                $icon = '✅';
                $pesanStatus = "Lolos Akademik! Sekarang menunggu verifikasi <b>Kasubag Sarpras</b>.";
                break;
            case 'disetujui':
                $icon = '🎉';
                $pesanStatus = "Selamat! Kegiatan kamu <b>DISETUJUI</b> sepenuhnya. Silakan cek aplikasi untuk detailnya.";
                break;
            case 'ditolak':
                $icon = '❌';
                $pesanStatus = "Mohon maaf, kegiatan kamu <b>DITOLAK</b>.\nAlasan: <i>{$validated['notes']}</i>";
                break;
            default:
                if (\Str::startsWith($newStatus, 'revisi_')) {
                    $icon = '⚠️';
                    $pesanStatus = "Terdapat permintan <b>REVISI</b>.\nCatatan: <i>{$validated['notes']}</i>\nSilakan perbaiki data kamu.";
                }
        }

        if (!empty($pesanStatus)) {
            $message = "{$icon} <b>Update Status Kegiatan</b>\n\n" .
                       "Judul Kegiatan: <b>{$kegiatan->nama_kegiatan}</b>\n" .
                       "Status: {$pesanStatus}\n\n" .
                       "<i>Sistem Layanan Sarana Prasarana FTMM</i>";
            
            // Kirim!
            $telegram->sendMessage($kegiatan->user->telegram_chat_id, $message);
        }
    }

    // Kirimkan pesan sukses yang sesuai dengan status baru
    // Simpan history khusus untuk aksi ini
    \App\Models\KegiatanHistory::create([
        'kegiatan_id' => $kegiatan->id,
        'user_id' => auth()->id(),
        'action' => $newStatus, // simpan action sesuai status baru atau 'revisi'
        'note' => $validated['notes'] ?? null,
        'meta' => json_encode(['action' => $validated['action'] ?? null, 'level' => $kegiatan->revisi_level ?? null]),
        'created_at' => now(),
    ]);

    return redirect()
        ->route('admin.kegiatan.index')
                ->with('success', $successMessage);
            }
        
            public function pinjamBarang(Request $request, Kegiatan $kegiatan)
            {
                $request->validate([
                    'barang_id' => 'required|exists:barangs,id',
                    'jumlah' => 'required|integer|min:1',
                ]);
        
                $barang = Barang::find($request->barang_id);
                $jumlahPinjam = $request->jumlah;
        
                if ($jumlahPinjam > $barang->stok) {
                    return back()->with('error', 'Stok barang tidak mencukupi.');
                }
        
                // Cek apakah barang ini sedang dalam status 'dipinjam' untuk kegiatan ini
                $borrowedItem = $kegiatan->barangs()
                    ->where('barang_id', $barang->id)
                    ->wherePivot('status', 'dipinjam')
                    ->first();
        
                if ($borrowedItem) {
                    // Jika sudah ada dan statusnya 'dipinjam', update jumlahnya
                    $borrowedItem->pivot->jumlah += $jumlahPinjam;
                    $borrowedItem->pivot->save();
                } else {
                    // Jika belum ada (atau sudah dikembalikan), buat record peminjaman baru
                    $kegiatan->barangs()->attach($barang->id, ['jumlah' => $jumlahPinjam, 'status' => 'dipinjam']);
                }
        
                $barang->stok -= $jumlahPinjam;
                $barang->save();
        
                return back()->with('success', 'Barang berhasil dipinjam.');
            }        
            public function kembalikanBarang(Request $request, Kegiatan $kegiatan, Barang $barang)
            {
                $borrowedItem = $kegiatan->barangs()->where('barang_id', $barang->id)->wherePivot('status', 'dipinjam')->first();
    
                if (!$borrowedItem) {
                    return back()->with('error', 'Data peminjaman tidak ditemukan atau sudah dikembalikan.');
                }
    
                $jumlahKembali = $borrowedItem->pivot->jumlah;
    
                // Update status on the specific pivot record
                $borrowedItem->pivot->status = 'dikembalikan';
                $borrowedItem->pivot->save();
    
                // Kembalikan stok barang
                $barang->stok += $jumlahKembali;
                $barang->save();
    
                return back()->with('success', 'Barang berhasil dikembalikan.');
            }        }
        
