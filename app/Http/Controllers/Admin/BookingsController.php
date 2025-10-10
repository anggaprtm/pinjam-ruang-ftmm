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
    protected $eventService;

    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
    }

    public function cariRuang(Request $request)
    {
        $ruangan = null;

        if ($request->filled(['waktu_mulai', 'waktu_selesai', 'kapasitas'])) {
            $ruangan = Ruangan::where('kapasitas', '>=', $request->input('kapasitas'))
                ->where('is_active', true)
                ->get()
                ->filter(function ($r) use ($request) {
                    $requestData = [
                        'ruangan_id'      => $r->id,
                        'waktu_mulai'     => $request->input('waktu_mulai'),
                        'waktu_selesai'   => $request->input('waktu_selesai'),
                        'tipe_berulang'   => $request->input('tipe_berulang'),
                        'berulang_sampai' => $request->input('berulang_sampai'),
                    ];

                    return !$this->eventService->isRoomTaken($requestData);
                });
        }

        return view('admin.bookings.cari', compact('ruangan'));
    }

    public function bookRuang(Request $request, EventService $eventService)
    {
        $request->merge([
            'user_id' => auth()->id()
        ]);

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

        $data = $request->all();
        $data['surat_izin'] = $suratIzinPath; 
        $data['status'] = auth()->user()->hasRole('Admin') ? 'disetujui' : 'belum_disetujui'; 
        $kegiatan = Kegiatan::create($data);
   
        $customEmails = ['angga.iryanto@staf.unair.ac.id']; // Email tambahan
        
        if (env('ENABLE_EMAIL_NOTIFICATIONS', true)) {
            Mail::to($customEmails)->send(new KegiatanNotification($kegiatan));
        }

        return redirect()->route('admin.kegiatan.index')->with('success','Proses book ruang berhasil dibuat.');
    }
}
