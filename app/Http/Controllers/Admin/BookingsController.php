<?php

namespace App\Http\Controllers\Admin;

use App\Models\Kegiatan;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKegiatanRequest;
use App\Models\Ruangan;
use App\Services\EventService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Mail\KegiatanNotification;
use Illuminate\Support\Facades\Mail;

class BookingsController extends Controller
{
    public function cariRuang(Request $request)
    {
        $ruangans = null;
        if($request->filled(['waktu_mulai', 'waktu_selesai', 'kapasitas'])) {
            $times = [
                Carbon::parse($request->input('waktu_mulai')),
                Carbon::parse($request->input('waktu_selesai')),
            ];

            $ruangans = Ruangan::where('kapasitas', '>=', $request->input('kapasitas'))
                ->where('is_active', true)
                ->whereDoesntHave('kegiatans', function ($query) use ($times) {
                    $query->whereBetween('waktu_mulai', $times)
                        ->orWhereBetween('waktu_selesai', $times)
                        ->orWhere(function ($query) use ($times) {
                            $query->where('waktu_mulai', '<', $times[0])
                                ->where('waktu_selesai', '>', $times[1]);
                        });
                })
                ->get();
        }

        return view('admin.bookings.cari', compact('ruangans'));
    }

    public function bookRuang(Request $request, EventService $eventService)
    {
        $request->merge([
            'user_id' => auth()->id()
        ]);

        // $request->validate([
        //     'nama_kegiatan'     => 'required',
        //     'ruangan_id'        => 'required',
        //     'surat_izin'        => 'required|file|mimes:pdf|max:2048',
        // ]);
        $rules = [
            'nama_kegiatan'     => 'required',
            'ruangan_id'        => 'required',
            'nomor_telepon'     => 'required|numeric|digits_between:10,13',
        ];

        if (!auth()->user()->isAdmin()) {
            $rules['surat_izin'] = 'required|file|mimes:pdf|max:2048';
        }

        $request->validate($rules);

        $ruangan = Ruangan::findOrFail($request->input('ruangan_id'));

        if ($eventService->isRoomTaken($request->all())) {
            return redirect()->back()
                    ->withInput($request->input())
                    ->withErrors('Ruangan ini tidak tersedia pada waktu tersebut.');
        }
        $suratIzinPath = null;
        if ($request->hasFile('surat_izin')) {
            $suratIzinPath = $request->file('surat_izin')->store('surat_izin', 'public');
        }
        // Gunakan array data baru
        $data = $request->all();
        $data['surat_izin'] = $suratIzinPath; // Masukkan path file yang benar ke dalam data
        $data['status'] = auth()->user()->hasRole('Admin') ? 'disetujui' : 'belum_disetujui'; // Status default untuk user
        $kegiatan = Kegiatan::create($data);
   
        $customEmails = ['angga.iryanto@staf.unair.ac.id']; // Email tambahan
        
        if (env('ENABLE_EMAIL_NOTIFICATIONS', true)) {
            Mail::to($customEmails)->send(new KegiatanNotification($kegiatan));
        }

        return redirect()->route('admin.kegiatans.index')->with('success','Proses book ruang berhasil dibuat.');
    }
}
