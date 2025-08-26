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
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class KegiatanController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('kegiatan_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = Kegiatan::with(['ruangan', 'user']);

        // Filter berdasarkan tanggal mulai
        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('waktu_mulai', '>=', $request->tanggal_mulai);
        }

        // Filter berdasarkan peminjam (user_id)
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter berdasarkan ruangan (ruangan_id)
        if ($request->filled('ruangan_id')) {
            $query->where('ruangan_id', $request->ruangan_id);
        }

        $kegiatan = $query->orderBy('id', 'desc')->get();

        // Ambil data untuk dropdown filter
        $users = User::pluck('name', 'id')->prepend('Semua Peminjam', '');
        $ruangans = Ruangan::pluck('nama', 'id')->prepend('Semua Ruangan', '');

        return view('admin.kegiatan.index', compact('kegiatan', 'users', 'ruangans'));
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
        
        // Panggil method baru yang menangani semuanya
        $eventService->createEvents($data);

        return redirect()->route('admin.kegiatan.index')->with('success', 'Kegiatan berhasil disimpan!');
    }

    public function edit(Kegiatan $kegiatan)
    {
        abort_if(Gate::denies('kegiatan_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $ruangan = Ruangan::pluck('nama', 'id')->prepend(trans('global.pleaseSelect'), '');

        $users = User::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        $kegiatan->load('ruangan', 'user');

        return view('admin.kegiatan.edit', compact('kegiatan', 'ruangan', 'users'));
    }

    public function update(UpdateKegiatanRequest $request, Kegiatan $kegiatan)
    {
        // Ambil semua data dari request
        $data = $request->all();

        // Proses file surat izin jika ada
        if ($request->hasFile('surat_izin')) {
            // Hapus surat izin lama jika ada
            if ($kegiatan->surat_izin && \Storage::disk('public')->exists($kegiatan->surat_izin)) {
                \Storage::disk('public')->delete($kegiatan->surat_izin);
            }

            // Simpan surat izin baru dan dapatkan path yang benar
            $data['surat_izin'] = $request->file('surat_izin')->store('surat_izin', 'public');
        }

        // Update kegiatan dengan data yang sudah dimodifikasi
        $kegiatan->update($data);

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

        $kegiatan->load('ruangan', 'user');

        return view('admin.kegiatan.show', compact('kegiatan'));
    }

    public function destroy(Kegiatan $kegiatan)
    {
        abort_if(Gate::denies('kegiatan_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

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
        'action' => 'required|in:next,back,reject', // Aksi yang harus dilakukan
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

        default:
            return redirect()->back()->with('error', 'Aksi tidak valid!');
    }

    // Simpan perubahan status dan notes
    $kegiatan->status = $newStatus;
    if (!empty($validated['notes'])) {
        $kegiatan->notes = $validated['notes'];
    }
    $kegiatan->save();

    // Kirimkan pesan sukses yang sesuai dengan status baru
    return redirect()
        ->route('admin.kegiatan.index')
        ->with('success', $successMessage);
    }



}
