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

            $query = Kegiatan::with(['ruangan', 'user'])->select(sprintf('%s.*', (new Kegiatan())->table));

            if ($request->filled('tanggal_mulai')) {
            $query->whereDate('waktu_mulai', '=', $request->tanggal_mulai);
            }
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }
            if ($request->filled('ruangan_id')) {
                $query->where('ruangan_id', $request->ruangan_id);
            }

            if (auth()->user()->hasRole('User')) {
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

            $table->editColumn('actions', function ($row) {
                $buttons = '';

                if (auth()->user()->can('kegiatan_show')) {
                    $buttons .= '<a class="btn btn-xs btn-info" href="' . route('admin.kegiatan.show', $row->id) . '" title="Detail"><i class="fas fa-eye"></i></a> ';
                }

                if (!auth()->user()->hasRole('User') || (auth()->user()->hasRole('User') && !in_array($row->status, ['disetujui', 'ditolak']))) {
                    if (auth()->user()->can('kegiatan_edit')) {
                        $buttons .= '<a class="btn btn-xs btn-success" href="' . route('admin.kegiatan.edit', $row->id) . '" title="Edit"><i class="fas fa-edit"></i></a> ';
                    }

                    if (auth()->user()->can('kegiatan_delete')) {
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

                switch ($row->status) {
                    case 'belum_disetujui':
                        return '<button type="button" class="btn btn-primary btn-sm js-open-modal" data-action-type="verifikasi_sarpras" data-id="'.$row->id.'">Verifikasi</button>';
                    case 'verifikasi_sarpras':
                        return '<button type="button" class="btn btn-primary btn-sm js-open-modal" data-action-type="verifikasi_akademik" data-id="'.$row->id.'">Verifikasi</button>';
                    case 'verifikasi_akademik':
                        $setujuiBtn = '<button type="button" class="btn btn-success btn-sm js-open-modal" data-action-type="setujui" data-id="'.$row->id.'">Setujui</button>';
                        $tolakBtn = '<button type="button" class="btn btn-danger btn-sm js-open-modal ms-1" data-action-type="tolak" data-id="'.$row->id.'">Tolak</button>';
                        return $setujuiBtn . $tolakBtn;
                    case 'disetujui':
                        // Cek apakah kegiatan ini disetujui langsung oleh admin saat dibuat
                        if (!$row->verifikasi_sarpras_at && !$row->verifikasi_akademik_at && !$row->disetujui_at) {
                            return '<span class="text-muted fst-italic"><i class="fas fa-user-shield me-1"></i> Kegiatan dibuat oleh Admin</span>';
                        }
                        // Jika disetujui melalui proses normal, tampilkan strip
                        return '<span class="text-muted">-</span>';
                    default:
                        return '<span class="text-muted">-</span>';
                }
            });

            // Di rawColumns, tambahkan 'persetujuan' dan 'placeholder'
            $table->rawColumns(['actions', 'placeholder', 'persetujuan']);

            // Mengembalikan data dalam format JSON
            return $table->make(true);
        }

        // Ambil data untuk dropdown filter (logika lama Anda dipertahankan)
        $users = User::pluck('name', 'id')->prepend('Semua Peminjam', '');
        $ruangans = Ruangan::pluck('nama', 'id')->prepend('Semua Ruangan', '');

        // Kita tidak mengirim 'kegiatan' lagi, karena akan diambil via AJAX
        return view('admin.kegiatan.index', compact('users', 'ruangans'));
    }

    public function create()
    {
        abort_if(Gate::denies('kegiatan_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $ruangan = Ruangan::pluck('nama', 'id')->prepend(trans('global.pleaseSelect'), '');
        $users = User::pluck('name', 'id');

        return view('admin.kegiatan.create', compact('ruangan', 'users'));
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
        
        // Logika status dan user_id tetap sama
        if (auth()->user()->hasRole('User')) {
            $data['status'] = 'belum_disetujui';
        } elseif (auth()->user()->hasRole('Admin')) {
            $data['status'] = 'disetujui';
        }
        
        // Panggil method yang membuat event(s) dan kembalikan model yang dibuat
        $created = $eventService->createEvents($data);

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
                    $kegiatan->status = 'belum_disetujui';
                    break;
                case 'revisi_sarpras':
                    $kegiatan->status = 'verifikasi_sarpras';
                    break;
                case 'revisi_akademik':
                    $kegiatan->status = 'verifikasi_akademik';
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
            switch ($kegiatan->status) {
                case 'belum_disetujui':
                    $newStatus = 'verifikasi_sarpras';
                    $kegiatan->verifikasi_sarpras_at = now();
                    $successMessage = 'Verifikasi berhasil, verifikasi selanjutnya ditangguhkan ke pihak Akademik.';
                    break;
                case 'verifikasi_sarpras':
                    $newStatus = 'verifikasi_akademik';
                    $kegiatan->verifikasi_akademik_at = now();
                    $successMessage = 'Verifikasi Akademik berhasil, verifikasi selanjutnya ditangguhkan ke pihak Sarpras.';
                    break;
                case 'verifikasi_akademik':
                    $newStatus = 'disetujui';
                    $kegiatan->disetujui_at = now();
                    $successMessage = 'Verifikasi berhasil, kegiatan telah disetujui!';
                    break;
            }
            break;

        case 'back':
            switch ($kegiatan->status) {
                case 'verifikasi_sarpras':
                    $newStatus = 'belum_disetujui';
                    $kegiatan->verifikasi_sarpras_at = null;
                    $successMessage = 'Verifikasi sarpras dibatalkan, dikembalikan ke Akademik.';
                    break;
                case 'verifikasi_akademik':
                    $newStatus = 'verifikasi_sarpras';
                    $kegiatan->verifikasi_akademik_at = null;
                    $successMessage = 'Verifikasi akademik dibatalkan, dikembalikan ke Operator.';
                    break;
            }
            break;

        case 'reject':
            $newStatus = 'ditolak';
            $kegiatan->ditolak_at = now();
            $successMessage = 'Kegiatan ditolak.';
            break;

        case 'revise':
            // Tetapkan status revisi berdasarkan tahapan saat ini
            switch ($kegiatan->status) {
                case 'belum_disetujui':
                    $newStatus = 'revisi_operator';
                    $successMessage = 'Permintaan revisi dikirim ke pemohon (Operator).';
                    break;
                case 'verifikasi_sarpras':
                    $newStatus = 'revisi_sarpras';
                    $successMessage = 'Permintaan revisi dikirim ke pemohon (Sarpras).';
                    break;
                case 'verifikasi_akademik':
                    $newStatus = 'revisi_akademik';
                    $successMessage = 'Permintaan revisi dikirim ke pemohon (Akademik).';
                    break;
                default:
                    $newStatus = 'revisi_operator';
                    $successMessage = 'Permintaan revisi dikirim ke pemohon.';
            }
            // Pastikan notes tersedia untuk revisi
            if (empty($validated['notes'])) {
                return redirect()->back()->with('error', 'Mohon isi catatan/revisi sebelum mengirim permintaan revisi.');
            }
            // Simpan informasi audit revisi
            $kegiatan->revisi_by = auth()->id();
            $kegiatan->revisi_at = now();
            // simpan level sesuai status yang ditetapkan
            $level = str_replace('revisi_', '', $newStatus);
            $kegiatan->revisi_level = $level;
            $kegiatan->revisi_notes = $validated['notes'];
            // juga simpan di notes umum agar terlihat di tampilan kegiatan
            $kegiatan->notes = $validated['notes'];
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
        
