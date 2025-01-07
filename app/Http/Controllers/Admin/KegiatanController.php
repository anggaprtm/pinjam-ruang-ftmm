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
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class KegiatanController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('kegiatan_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Ambil parameter tanggal mulai dari request
        $tanggalMulai = $request->input('tanggal_mulai');

        // Query kegiatan dengan relasi ruangan dan user
        $query = Kegiatan::with(['ruangan', 'user']);

        // Filter berdasarkan role
        if (auth()->user()->isUser()) {
            $query->where('user_id', auth()->id()); // Tampilkan hanya kegiatan milik user yang login
        }

        // Filter tanggal mulai jika ada
        if ($tanggalMulai) {
            $query->whereDate('waktu_mulai', $tanggalMulai);
        }

        // Eksekusi query
        $kegiatans = $query->get();

        return view('admin.kegiatans.index', compact('kegiatans', 'tanggalMulai'));
    }

    public function create()
    {
        abort_if(Gate::denies('kegiatan_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $ruangans = Ruangan::pluck('nama', 'id')->prepend(trans('global.pleaseSelect'), '');
        $users = User::pluck('name', 'id');

        return view('admin.kegiatans.create', compact('ruangans', 'users'));
    }

    public function store(StoreKegiatanRequest $request, EventService $eventService)
    {
        // if ($eventService->isRoomTaken($request->all())) {
        //     return redirect()->back()
        //             ->withInput($request->input())
        //             ->withErrors('Ruangan ini tidak tersedia pada waktu tersebut.');
        // }
        {
            $kegiatanBentrok = $eventService->isRoomTaken($request->all());
        
            if ($kegiatanBentrok) {
                return redirect()->back()
                        ->withInput($request->input())
                        ->withErrors('Ruangan ini tidak tersedia, karena bentrok dengan kegiatan: ' . $kegiatanBentrok->nama_kegiatan);
            }
        
            // Jika tidak ada bentrokan, lanjutkan penyimpanan
        }
         // awal perubahan
        $data = $request->all();
        if ($data['user_id'] == 'custom') {
            $data['user_id'] = null;  // Set user_id sebagai null
        }
        // Tambahkan status default untuk user biasa
        if (auth()->user()->role === 'user') {
            $data['status'] = 'belum_disetujui'; // Status default untuk user biasa
        } else {
            $data['status'] = 'disetujui'; // Admin langsung menyetujui
        }
        $kegiatan = Kegiatan::create($data);
        if ($request->filled('berulang_sampai')) {
            $eventService->createRecurringEvents($data);
        }

        return redirect()->route('admin.kegiatans.index');
    }

    public function edit(Kegiatan $kegiatan)
    {
        abort_if(Gate::denies('kegiatan_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $ruangans = Ruangan::pluck('nama', 'id')->prepend(trans('global.pleaseSelect'), '');

        $users = User::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        $kegiatan->load('ruangan', 'user');

        return view('admin.kegiatans.edit', compact('kegiatan', 'ruangans', 'users'));
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

        return redirect()->route('admin.kegiatans.index')->with('success', 'Kegiatan berhasil diperbarui.');
    }
    // public function update(UpdateKegiatanRequest $request, Kegiatan $kegiatan)
    // {
    //     $data = $request->all();
    //     // Proses file surat izin jika ada
    //     if ($request->hasFile('surat_izin')) {
    //         // Hapus surat izin lama jika ada
    //         if ($kegiatan->surat_izin && \Storage::disk('public')->exists($kegiatan->surat_izin)) {
    //             \Storage::disk('public')->delete($kegiatan->surat_izin);
    //         }

    //         $data['surat_izin'] = $request->file('surat_izin')->store('surat_izin','public')
    //     }
    
    //     $kegiatan->update($data);

    //     return redirect()->route('admin.kegiatans.index');
    // }
    
    public function editSuratIzin(Kegiatan $kegiatan)
    {
        abort_if(Gate::denies('kegiatan_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.kegiatans.edit-surat-izin', compact('kegiatan'));
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

    return redirect()->route('admin.kegiatans.index')->with('success', 'Surat izin berhasil diperbarui.');
}

    public function show(Kegiatan $kegiatan)
    {
        abort_if(Gate::denies('kegiatan_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $kegiatan->load('ruangan', 'user');

        return view('admin.kegiatans.show', compact('kegiatan'));
    }

    public function destroy(Kegiatan $kegiatan)
    {
        abort_if(Gate::denies('kegiatan_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $kegiatan->delete();

        return back();
    }

    public function massDestroy(MassDestroyKegiatanRequest $request)
    {
        $kegiatans = Kegiatan::find(request('ids'));

        foreach ($kegiatans as $kegiatan) {
            $kegiatan->delete();
        }

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function updateStatus(Request $request, Kegiatan $kegiatan)
    {
        // Validasi input
        $validated = $request->validate([
            'status' => 'required|in:belum_disetujui,disetujui,ditolak',
        ]);

        // Perbarui status kegiatan
        $kegiatan->update([
            'status' => $validated['status'],
        ]);

        return redirect()->route('admin.kegiatans.index')->with('success', 'Status kegiatan berhasil diperbarui.');
    }

}