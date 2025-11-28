<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ruangan;
use App\Services\EventService;
use Illuminate\Http\Request;
use App\Mail\KegiatanNotification;
use Illuminate\Support\Facades\Mail;
use App\Models\Kegiatan;

class BookingsController extends Controller
{
    protected $eventService;

    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
    }

    public function cariRuang(Request $request)
    {
        // Ganti nama variabel agar konsisten
        $ruangan = collect(); // Mulai dengan koleksi kosong

        // Cek apakah ada filter waktu dan kapasitas yang diisi
        if ($request->filled(['waktu_mulai', 'waktu_selesai', 'kapasitas'])) {
            // --- LOGIKA PENCARIAN (FILTER) ---
            $request->validate([
                'waktu_mulai' => 'required|date_format:' . config('panel.date_format') . ' ' . config('panel.time_format'),
                'waktu_selesai' => 'required|date_format:' . config('panel.date_format') . ' ' . config('panel.time_format') . '|after:waktu_mulai',
                'kapasitas' => 'required|integer|min:1',
            ]);

            // Gunakan logika filter Anda yang sudah ada
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
        } else {
            $ruangan = Ruangan::where('is_active', true)->orderBy('nama', 'asc')->get();
        }

        return view('admin.bookings.cari', ['ruangan' => $ruangan]);
    }

    // Method bookRuang Anda tidak perlu diubah
    public function bookRuang(Request $request)
    {
        $request->merge([ 'user_id' => auth()->id() ]);

        $rules = [
            'nama_kegiatan'   => 'required',
            'ruangan_id'      => 'required',
            // validate waktu fields passed from the search form
            'waktu_mulai'     => 'required|date_format:' . config('panel.date_format') . ' ' . config('panel.time_format'),
            'waktu_selesai'   => 'required|date_format:' . config('panel.date_format') . ' ' . config('panel.time_format') . '|after:waktu_mulai',
            'nama_pic'      => ['required', 'string'],
            'nomor_telepon' => [
                                    'required',
                                    'string',
                                    'regex:/^0[0-9]+$/',
                                    'min:9',
                                    'max:15',
                                ],

        ];

        if (!auth()->user()->isAdmin()) {
            $rules['surat_izin'] = 'required|file|mimes:pdf|max:2048';
        }

        $request->validate($rules);

        if ($this->eventService->isRoomTaken($request->all())) {
            return redirect()->back()->withInput($request->input())->withErrors('Ruangan ini tidak tersedia pada waktu tersebut.');
        }
        
        $suratIzinPath = null;
        if ($request->hasFile('surat_izin')) {
            $suratIzinPath = $request->file('surat_izin')->store('surat_izin', 'public');
        }

        $data = $request->all();
        $data['surat_izin'] = $suratIzinPath; 
        $data['status'] = auth()->user()->isAdmin() ? 'disetujui' : 'belum_disetujui'; 
        $kegiatan = Kegiatan::create($data);
   
        // $customEmails = ['angga.iryanto@staf.unair.ac.id'];
        
        // if (env('ENABLE_EMAIL_NOTIFICATIONS', true)) {
        //     Mail::to($customEmails)->send(new KegiatanNotification($kegiatan));
        // }

        return redirect()->route('admin.kegiatan.index')->with('success','Proses book ruang berhasil dibuat.');
    }
}