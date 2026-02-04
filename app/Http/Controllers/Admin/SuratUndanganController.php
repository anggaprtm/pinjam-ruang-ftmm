<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class SuratUndanganController extends Controller
{
    // Preset Pejabat
    private $penandatangans = [
        [
            'jabatan' => 'Dekan',
            'nama' => 'Prof. Dr. Ir. Retna Apsari, M.Si., IPM., ASEAN Eng.',
            'nip' => '196806261993032003'
        ],
        [
            'jabatan' => 'Wakil Dekan I',
            'nama' => 'Prof. Dr. Ni\'matuzahroh',
            'nip' => '196801051992032003'
        ],
        [
            'jabatan' => 'Wakil Dekan II',
            'nama' => 'Fadli Ama, S.T., M.T.',
            'nip' => '197512062008121002'
        ],
        [
            'jabatan' => 'Wakil Dekan III',
            'nama' => 'Prastika Krisma Jiwanti, S.Si., M.Sc.Eng., Ph.D.',
            'nip' => '199104192019083201'
        ],
    ];

    // Preset Tujuan (Pilihan Baku)
    private $tujuanPresets = [
        'Para Wakil Dekan',
        'Kepala Bagian Tata Usaha',
        'Para Koordinator Program Studi',
        'Para Kepala Sub Bagian',
        'Ketua Satuan Penjaminan Mutu',
        'Sekretaris Satuan Penjaminan Mutu',
        'Staf Akademik dan Kemahasiswaan',
    ];

    // Preset Tempat
    private $tempatPresets = [
        'Ruang Rapat Lt. 10 Gedung Nano Kampus C Universitas Airlangga',
        'Ruang Sidang Lt. 3 Gedung Nano',
        'Auditorium Candradimuka',
        'Zoom Meeting (Daring)',
    ];

    public function create()
    {
        $penandatangans = $this->penandatangans;
        $tujuanPresets  = $this->tujuanPresets;
        $tempatPresets  = $this->tempatPresets;

        return view('admin.surat.create', compact('penandatangans', 'tujuanPresets', 'tempatPresets'));
    }

    public function preview(Request $request)
    {
        // Validasi
        $request->validate([
            'nomor_surat' => 'required',
            // 'tujuan_surat' => 'required', // Array user
        ]);

        $data = $this->processData($request);
        
        // Flag untuk view bahwa ini mode preview HTML (bukan PDF)
        $is_pdf = false; 

        return view('admin.surat.template_pdf', compact('data', 'is_pdf'))->render();
    }

    public function store(Request $request)
    {
        $data = $this->processData($request);
        
        // Flag untuk view bahwa ini mode PDF
        $is_pdf = true;

        $pdf = Pdf::loadView('admin.surat.template_pdf', compact('data', 'is_pdf'))
                  ->setPaper('a4', 'portrait');

        $fileName = 'Undangan-' . str_replace('/', '-', $request->nomor_surat) . '.pdf';
        return $pdf->download($fileName);
    }

    // Helper untuk memproses data input sebelum dikirim ke View
    private function processData($request)
    {
        $data = $request->all();

        // 1. LOGIC TANGGAL OTOMATIS (Datepicker -> Hari, Tanggal)
        if ($request->filled('tanggal_acara_raw')) {
            // Set locale ID biar jadi "Rabu", "Mei"
            Carbon::setLocale('id'); 
            $data['hari_tanggal_acara'] = Carbon::parse($request->tanggal_acara_raw)
                ->translatedFormat('l, d F Y');
        } else {
            $data['hari_tanggal_acara'] = '-';
        }

        // 2. LOGIC TUJUAN (Array dari Select2 -> List String)
        if ($request->has('tujuan_surat') && is_array($request->tujuan_surat)) {
            // Gabungkan array jadi string dengan <br> atau numbering
            // Kita biarkan array biar di view bisa di-looping pakai <ol>
            $data['tujuan_surat_list'] = $request->tujuan_surat;
        } else {
            $data['tujuan_surat_list'] = [];
        }

        // 3. LOGIC PENANDATANGAN
        if ($request->has('penandatangan_index')) {
            $signer = $this->penandatangans[$request->penandatangan_index] ?? $this->penandatangans[0];
            $data['jabatan_penandatangan'] = $signer['jabatan'];
            $data['nama_penandatangan']    = $signer['nama'];
            $data['nip_penandatangan']     = $signer['nip'];
        }

        return $data;
    }
}