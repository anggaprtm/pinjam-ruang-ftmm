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

            if (auth()->user()->hasRole('User') || auth()->user()->hasRole('Pegawai')) {
                $query->where('kegiatan.user_id', auth()->id());
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
                $buttons = '';
                $user = auth()->user();

                // 1. Tombol DETAIL (Semua bisa lihat jika punya izin)
                if ($user->can('kegiatan_show')) {
                    $buttons .= '<a class="btn btn-xs btn-info" href="' . route('admin.kegiatan.show', $row->id) . '" title="Detail"><i class="fas fa-eye"></i></a> ';
                }

                // 2. Logic Tombol EDIT & HAPUS
                // Default: Izinkan tampil (untuk Admin/Verifikator)
                $allowEditDelete = true;

                // FILTER KHUSUS ROLE USER (PEMINJAM)
                if ($user->hasRole('User')) {
                    // Daftar status "Aman" untuk diedit user
                    $editableStatuses = [
                        'belum_disetujui',          // Masih draft awal
                        'revisi_operator',          // Sedang direvisi
                        'revisi_kemahasiswaan',
                        'revisi_kasubag_akademik',
                        'revisi_kasubag_sarpras'
                    ];

                    // Jika status saat ini TIDAK ada di daftar editable, maka kunci tombol
                    if (!in_array($row->status, $editableStatuses)) {
                        $allowEditDelete = false;
                    }
                }

                // Render tombol jika lolos filter
                if ($allowEditDelete) {
                    // Cek permission Edit
                    if ($user->can('kegiatan_edit')) {
                        $buttons .= '<a class="btn btn-xs btn-success" href="' . route('admin.kegiatan.edit', $row->id) . '" title="Edit"><i class="fas fa-edit"></i></a> ';
                    }

                    // Cek permission Hapus
                    // (Biasanya kalau gak boleh edit, hapus juga gak boleh saat sedang diverifikasi)
                    if ($user->can('kegiatan_delete')) {
                        $buttons .= '<button type="button" class="btn btn-xs btn-danger js-delete-btn" data-url="' . route('admin.kegiatan.destroy', $row->id) . '" title="Hapus"><i class="fas fa-trash"></i></button>';
                    }
                }

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
                'waktu_mulai' => \Carbon\Carbon::parse($start)->format($format),
                'waktu_selesai' => \Carbon\Carbon::parse($end)->format($format),
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
        // Pengecekan bentrok tetap sama
        $kegiatanBentrok = $eventService->isRoomTaken($request->all());
    
        if ($kegiatanBentrok) {
            return redirect()->back()
                ->withInput($request->input())
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
        // Ambil semua data dari request
        // Tambahan: User tidak bisa update jika sudah disetujui
        if (auth()->user()->hasRole('User') && $kegiatan->status === 'disetujui') {
            abort(Response::HTTP_FORBIDDEN, '403 Forbidden: Anda tidak dapat mengubah data yang sudah disetujui.');
        }

        // Gabungkan nilai lama + yang baru (supaya payload lengkap untuk isRoomTaken)
        $payload = array_merge($kegiatan->toArray(), $request->all());
        $payload['ignore_id'] = $kegiatan->id;

        // Jika file surat_izin akan diganti, simpan path di payload dulu (tetap cek bentrok dulu)
        if ($request->hasFile('surat_izin')) {
            $payload['__new_surat_izin'] = true; // penanda sementara
        }

        $bentrok = $eventService->isRoomTaken($payload);
        if ($bentrok) {
            return back()->withInput($request->all())
                ->withErrors('Bentrok dengan kegiatan: ' . $bentrok->nama_kegiatan);
        }

        // Baru proses file (setelah aman)
        $data = $request->all();

        if ($request->hasFile('poster')) {
            // Hapus poster lama jika ada
            if ($kegiatan->poster && \Storage::disk('public')->exists($kegiatan->poster)) {
                \Storage::disk('public')->delete($kegiatan->poster);
            }
            // Upload yang baru
            $data['poster'] = $request->file('poster')->store('posters', 'public');
        }
        if ($request->hasFile('surat_izin')) {
            if ($kegiatan->surat_izin && \Storage::disk('public')->exists($kegiatan->surat_izin)) {
                \Storage::disk('public')->delete($kegiatan->surat_izin);
            }
            $data['surat_izin'] = $request->file('surat_izin')->store('surat_izin', 'public');
        }

        $oldStatus = $kegiatan->status;

        $kegiatan->update($data);

        // Tambahkan history untuk perubahan (edit oleh pemohon)
        \App\Models\KegiatanHistory::create([
            'kegiatan_id' => $kegiatan->id,
            'user_id' => auth()->id(),
            'action' => 'edited',
            'note' => 'Data kegiatan diperbarui',
            'meta' => null,
            'created_at' => now(),
        ]);

        // Jika ini adalah resubmisi setelah revisi, kembalikan status ke tahap verifikasi yang sesuai
        if (Str::startsWith($oldStatus, 'revisi_')) {
            switch ($oldStatus) {
                case 'revisi_operator':
                    // Jika revisi dari status awal
                    $kegiatan->status = 'belum_disetujui';
                    break;
                case 'revisi_kemahasiswaan':
                    // Balik ke meja Kemahasiswaan
                    $kegiatan->status = 'verifikasi_kemahasiswaan';
                    break;
                case 'revisi_kasubag_akademik':
                    // Balik ke meja Akademik
                    $kegiatan->status = 'verifikasi_kasubag_akademik';
                    break;
                case 'revisi_kasubag_sarpras':
                    // Balik ke meja Sarpras
                    $kegiatan->status = 'verifikasi_kasubag_sarpras';
                    break;
                default:
                    $kegiatan->status = 'belum_disetujui';
            }
            $kegiatan->save();
        }

        return redirect()->route('admin.kegiatan.index')->with('success', 'Kegiatan berhasil diperbarui.');
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

    public function destroy(Kegiatan $kegiatan)
    {
        abort_if(Gate::denies('kegiatan_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Tambahan: User tidak bisa hapus jika sudah disetujui
        if (auth()->user()->hasRole('User') && $kegiatan->status === 'disetujui') {
            abort(Response::HTTP_FORBIDDEN, '403 Forbidden: Anda tidak dapat menghapus data yang sudah disetujui.');
        }


        $kegiatan->delete();

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

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        try {
            Excel::import(new KegiatanImport, $request->file('file'));
            return redirect()->route('admin.kegiatan.index')->with('success', 'Data Sidang/Seminar berhasil diimport!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(new KegiatanTemplateExport, 'template_import_kegiatan.xlsx');
    }

    public function updateStatus(Request $request, Kegiatan $kegiatan)
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
        
