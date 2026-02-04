<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SuratUndangan; // Pastikan Model sudah di-import
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class SuratUndanganController extends Controller
{
    // Preset Pejabat
    private $penandatangans = [
        [
            'jabatan' => 'Dekan',
            'nama' => 'Prof. Dr. Ir. Retna Apsari, M.Si., IPM., ASEAN Eng.',
            'nip' => '196806261993032003',
            'ttd_image' => 'ttd_dekan.png'
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

    public function index(Request $request)
    {
        // 1. Handle AJAX DataTables
        if ($request->ajax()) {
            // Ambil semua data surat
            $query = SuratUndangan::query()->select(sprintf('%s.*', (new SuratUndangan)->getTable()));

            $table = DataTables::of($query);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            // Custom Column: Actions
            $table->editColumn('actions', function ($row) {
                // Tombol Download Ulang
                $btn = '<a class="btn btn-xs btn-warning" href="' . route('admin.surat-undangan.download', $row->id) . '" target="_blank" title="Download PDF"><i class="fas fa-print"></i></a> ';
                
                // Tombol Hapus (Optional: Tambah permission check jika perlu)
                $btn .= '<form action="'.route('admin.surat-undangan.destroy', $row->id).'" method="POST" onsubmit="return confirm(\'Hapus arsip surat ini?\');" style="display: inline-block;">
                            <input type="hidden" name="_method" value="DELETE">
                            <input type="hidden" name="_token" value="'.csrf_token().'">
                            <button type="submit" class="btn btn-xs btn-danger" title="Hapus"><i class="fas fa-trash-alt"></i></button>
                        </form>';
                return $btn;
            });

            // Format Tanggal
            $table->editColumn('tanggal_surat', function ($row) {
                return $row->tanggal_surat ? Carbon::parse($row->tanggal_surat)->translatedFormat('d M Y') : '';
            });

            // Format Penandatangan (Tampilkan Nama + Jabatan)
            $table->editColumn('nama_penandatangan', function ($row) {
                return '<div class="fw-bold">'.$row->nama_penandatangan.'</div><small class="text-muted">'.$row->jabatan_penandatangan.'</small>';
            });

            $table->rawColumns(['actions', 'placeholder', 'nama_penandatangan']);

            return $table->make(true);
        }

        return view('admin.surat.index');
    }


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
        
        // --- 1. SIMPAN KE DATABASE ---
        // Convert array list tujuan jadi string JSON atau Text biar bisa masuk DB
        $dbData = $data;
        $dbData['tujuan_surat'] = json_encode($data['tujuan_surat_list']); // Simpan sebagai JSON

         if (!empty($data['lampiran_table'])) {
            $dbData['lampiran_table'] = json_encode($data['lampiran_table']);
        }
        
        // Create Record
        $surat = SuratUndangan::create($dbData);

        // --- 2. GENERATE PDF ---
        // Kita redirect ke method download biar kodenya reusable
        return redirect()->route('admin.surat-undangan.download', $surat->id);
    }

    public function download($id)
    {
        $surat = SuratUndangan::findOrFail($id);
        
        $data = $surat->toArray();
        
        // ✅ Reconstruct tujuan_surat_list
        $data['tujuan_surat_list'] = json_decode($surat->tujuan_surat, true) ?? [];
        
        // ✅ FIX: Reconstruct lampiran_table dari JSON
        if (!empty($surat->lampiran_table)) {
            $data['lampiran_table'] = json_decode($surat->lampiran_table, true) ?? [];
        } else {
            $data['lampiran_table'] = [];
        }
        
        // ✅ FIX: Cast use_lampiran ke boolean
        // Karena dari database jadi integer 0/1
        $data['use_lampiran'] = (bool) ($surat->use_lampiran ?? false);
        
        $is_pdf = true;
        $pdf = Pdf::loadView('admin.surat.template_pdf', compact('data', 'is_pdf'))
                ->setPaper('a4', 'portrait');
        
        $fileName = 'Undangan-' . str_replace('/', '-', $surat->nomor_surat) . '.pdf';
        
        return $pdf->stream($fileName);
    }

    public function destroy($id)
    {
        $surat = SuratUndangan::findOrFail($id);
        $surat->delete();
        return back()->with('success', 'Arsip surat berhasil dihapus.');
    }
    // Helper untuk memproses data input sebelum dikirim ke View
    private function processData($request)
    {
        $data = $request->all();

        // 1. LOGIC TANGGAL OTOMATIS (Tetap Sama)
        if ($request->filled('tanggal_acara_raw')) {
            Carbon::setLocale('id'); 
            $data['hari_tanggal_acara'] = Carbon::parse($request->tanggal_acara_raw)
                ->translatedFormat('l, d F Y');
        } else {
            $data['hari_tanggal_acara'] = '-';
        }

        // ==========================================
        // 2. LOGIC TUJUAN & LAMPIRAN (BAGIAN BARU)
        // ==========================================
        
        // Cek mode tujuan dari radio button (default 'biasa' jika tidak ada)
        $mode = $request->input('mode_tujuan', 'biasa');
        $data['use_lampiran'] = ($mode === 'lampiran');

        if ($data['use_lampiran']) {
            // --- JIKA MODE LAMPIRAN ---
            
            // A. Di surat utama ditulis "Daftar Terlampir"
            $data['tujuan_surat_list'] = ['Daftar Terlampir']; 

            // B. Proses Textarea menjadi Array Table
            // Format input: "Nama - Jabatan" (dipisah enter)
            $rawContent = $request->input('lampiran_content', '');
            $rawLines = explode("\n", $rawContent); // Pecah per baris
            $parsedLampiran = [];
            
            foreach($rawLines as $line) {
                // Hanya proses baris yang tidak kosong
                if(trim($line) != "") {
                    // Pecah berdasarkan tanda strip "-" pertama
                    // Contoh: "Dr. Budi - Kaprodi" -> ["Dr. Budi ", " Kaprodi"]
                    $parts = explode('-', $line, 2); 
                    
                    $parsedLampiran[] = [
                        'nama' => trim($parts[0]),
                        // Jika tidak ada strip, jabatan diisi strip (-)
                        'jabatan' => isset($parts[1]) ? trim($parts[1]) : '-' 
                    ];
                }
            }
            
            $data['lampiran_table'] = $parsedLampiran;
            // Simpan konten mentah juga (buat jaga-jaga atau edit ulang)
            $data['lampiran_content'] = $rawContent;

        } else {
            // --- JIKA MODE BIASA (SELECT2) ---
            
            if ($request->has('tujuan_surat') && is_array($request->tujuan_surat)) {
                $data['tujuan_surat_list'] = $request->tujuan_surat;
            } else {
                $data['tujuan_surat_list'] = [];
            }
            
            // Pastikan data lampiran kosong biar gak error di view
            $data['lampiran_table'] = [];
            $data['lampiran_content'] = null;
        }

        // 3. LOGIC PENANDATANGAN (Tetap Sama)
        if ($request->has('penandatangan_index')) {
            $signer = $this->penandatangans[$request->penandatangan_index] ?? $this->penandatangans[0];
            
            $data['jabatan_penandatangan'] = $signer['jabatan'];
            $data['nama_penandatangan']    = $signer['nama'];
            $data['nip_penandatangan']     = $signer['nip'];
            
            // Logika TTD Digital
            if ($request->has('use_ttd') && !empty($signer['ttd_image'])) {
                $data['ttd_image'] = $signer['ttd_image'];
            } else {
                $data['ttd_image'] = null;
            }
        }

        return $data;
    }
}