<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SuratUndangan;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class SuratUndanganController extends Controller
{
    // ──────────────────────────────────────
    // PRESET DATA
    // ──────────────────────────────────────

    private array $penandatangans = [
        [
            'jabatan'   => 'Dekan',
            'nama'      => 'Prof. Dr. Ir. Retna Apsari, M.Si., IPM., ASEAN Eng.',
            'nip'       => '196806261993032003',
            'ttd_image' => 'ttd_dekan.png',
        ],
        [
            'jabatan' => 'Wakil Dekan I',
            'nama'    => 'Prof. Dr. Ni\'matuzahroh',
            'nip'     => '196801051992032003',
        ],
        [
            'jabatan' => 'Wakil Dekan II',
            'nama'    => 'Fadli Ama, S.T., M.T.',
            'nip'     => '197512062008121002',
        ],
        [
            'jabatan' => 'Wakil Dekan III',
            'nama'    => 'Prastika Krisma Jiwanti, S.Si., M.Sc.Eng., Ph.D.',
            'nip'     => '199104192019083201',
        ],
    ];

    private array $tujuanPresets = [
        'Para Wakil Dekan',
        'Kepala Bagian Tata Usaha',
        'Para Koordinator Program Studi',
        'Para Kepala Sub Bagian',
        'Ketua Satuan Penjaminan Mutu',
        'Sekretaris Satuan Penjaminan Mutu',
        'Staf Akademik dan Kemahasiswaan',
    ];

    private array $tempatPresets = [
        'Ruang Rapat Lt. 10 Gedung Nano Kampus C Universitas Airlangga',
        'Ruang Sidang Lt. 3 Gedung Nano',
        'Auditorium Candradimuka',
        'Zoom Meeting (Daring)',
    ];

    // ──────────────────────────────────────
    // INDEX
    // ──────────────────────────────────────

    public function index(Request $request)
    {
        // Handle AJAX DataTables
        if ($request->ajax()) {
            $query = SuratUndangan::query()->latest();

            return datatables()->of($query)
                ->addColumn('actions', function ($row) {
                    $download = '<a class="btn btn-xs btn-warning" href="' . route('admin.surat-undangan.download', $row->id) . '" target="_blank" title="Download PDF"><i class="fas fa-print"></i></a> ';
                    $edit     = '<a class="btn btn-xs btn-info" href="' . route('admin.surat-undangan.edit', $row->id) . '" title="Edit"><i class="fas fa-edit"></i></a> ';
                    $delete   = '<form action="' . route('admin.surat-undangan.destroy', $row->id) . '" method="POST" class="d-inline" onsubmit="return confirm(\'Hapus arsip surat ini?\');">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <input type="hidden" name="_token" value="' . csrf_token() . '">
                                    <button type="submit" class="btn btn-xs btn-danger" title="Hapus"><i class="fas fa-trash-alt"></i></button>
                                </form>';
                    return $download . $edit . $delete;
                })
                ->editColumn('tanggal_surat', fn($row) =>
                    $row->tanggal_surat ? Carbon::parse($row->tanggal_surat)->translatedFormat('d M Y') : '-'
                )
                ->editColumn('tujuan_surat', function ($row) {
                    $list = json_decode($row->tujuan_surat, true) ?? [];
                    if (count($list) > 2) {
                        return $list[0] . ', ' . $list[1] . ' <span class="badge bg-secondary">+' . (count($list) - 2) . ' lainnya</span>';
                    }
                    return implode(', ', $list);
                })
                ->editColumn('nama_penandatangan', fn($row) =>
                    '<div class="fw-bold">' . $row->nama_penandatangan . '</div><small class="text-muted">' . $row->jabatan_penandatangan . '</small>'
                )
                ->editColumn('agenda_acara', fn($row) =>
                    $row->agenda_acara ? \Str::limit($row->agenda_acara, 60) : '-'
                )
                ->rawColumns(['actions', 'nama_penandatangan', 'tujuan_surat'])
                ->make(true);
        }

        return view('admin.surat-undangan.index');
    }

    // ──────────────────────────────────────
    // CREATE
    // ──────────────────────────────────────

    public function create()
    {
        return view('admin.surat-undangan.create', [
            'penandatangans' => $this->penandatangans,
            'tujuanPresets'  => $this->tujuanPresets,
            'tempatPresets'  => $this->tempatPresets,
        ]);
    }

    // ──────────────────────────────────────
    // STORE
    // ──────────────────────────────────────

    public function store(Request $request)
    {
        $request->validate([
            'nomor_surat'       => ['required', 'string', 'max:255'],
            'tanggal_surat'     => ['required', 'date'],
            'hal_surat'         => ['nullable', 'string', 'max:255'],
            'tanggal_acara_raw' => ['required', 'date'],
            'waktu_acara'       => ['required', 'string', 'max:100'],
            'tempat_acara'      => ['required', 'string', 'max:255'],
            'agenda_acara'      => ['required', 'string'],
        ]);

        $data = $this->processData($request);

        $record = SuratUndangan::create([
            'nomor_surat'            => $data['nomor_surat'],
            'tanggal_surat'          => $data['tanggal_surat'],
            'hal_surat'              => $data['hal_surat'] ?? 'Undangan',
            'tujuan_surat'           => json_encode($data['tujuan_surat_list']),
            'hari_tanggal_acara'     => $data['hari_tanggal_acara'],
            'waktu_acara'            => $data['waktu_acara'],
            'tempat_acara'           => $data['tempat_acara'],
            'agenda_acara'           => $data['agenda_acara'],
            'dresscode'              => $data['dresscode'] ?? null,
            'jabatan_penandatangan'  => $data['jabatan_penandatangan'],
            'nama_penandatangan'     => $data['nama_penandatangan'],
            'nip_penandatangan'      => $data['nip_penandatangan'],
            'use_lampiran'           => $data['use_lampiran'],
            'lampiran_content'       => $data['lampiran_content'] ?? null,
            'lampiran_table'         => !empty($data['lampiran_table']) ? json_encode($data['lampiran_table']) : null,
        ]);

        return redirect()->route('admin.surat-undangan.download', $record->id);
    }

    // ──────────────────────────────────────
    // EDIT
    // ──────────────────────────────────────

    public function edit(SuratUndangan $suratUndangan)
    {
        // Decode data untuk re-populate form
        $suratUndangan->tujuan_list   = json_decode($suratUndangan->tujuan_surat, true) ?? [];
        $suratUndangan->lampiran_list = json_decode($suratUndangan->lampiran_table, true) ?? [];

        return view('admin.surat-undangan.edit', [
            'surat'          => $suratUndangan,
            'penandatangans' => $this->penandatangans,
            'tujuanPresets'  => $this->tujuanPresets,
            'tempatPresets'  => $this->tempatPresets,
        ]);
    }

    // ──────────────────────────────────────
    // UPDATE
    // ──────────────────────────────────────

    public function update(Request $request, SuratUndangan $suratUndangan)
    {
        $request->validate([
            'nomor_surat'       => ['required', 'string', 'max:255'],
            'tanggal_surat'     => ['required', 'date'],
            'tanggal_acara_raw' => ['required', 'date'],
            'waktu_acara'       => ['required', 'string', 'max:100'],
            'tempat_acara'      => ['required', 'string', 'max:255'],
            'agenda_acara'      => ['required', 'string'],
        ]);

        $data = $this->processData($request);

        $suratUndangan->update([
            'nomor_surat'            => $data['nomor_surat'],
            'tanggal_surat'          => $data['tanggal_surat'],
            'hal_surat'              => $data['hal_surat'] ?? 'Undangan',
            'tujuan_surat'           => json_encode($data['tujuan_surat_list']),
            'hari_tanggal_acara'     => $data['hari_tanggal_acara'],
            'waktu_acara'            => $data['waktu_acara'],
            'tempat_acara'           => $data['tempat_acara'],
            'agenda_acara'           => $data['agenda_acara'],
            'dresscode'              => $data['dresscode'] ?? null,
            'jabatan_penandatangan'  => $data['jabatan_penandatangan'],
            'nama_penandatangan'     => $data['nama_penandatangan'],
            'nip_penandatangan'      => $data['nip_penandatangan'],
            'use_lampiran'           => $data['use_lampiran'],
            'lampiran_content'       => $data['lampiran_content'] ?? null,
            'lampiran_table'         => !empty($data['lampiran_table']) ? json_encode($data['lampiran_table']) : null,
        ]);

        return redirect()->route('admin.surat-undangan.download', $suratUndangan->id);
    }

    // ──────────────────────────────────────
    // PREVIEW (AJAX)
    // ──────────────────────────────────────

    public function preview(Request $request)
    {
        $request->validate(['nomor_surat' => ['required']]);

        $data   = $this->processData($request);
        $is_pdf = false;

        return view('admin.surat-undangan.template_pdf', compact('data', 'is_pdf'))->render();
    }

    // ──────────────────────────────────────
    // DOWNLOAD PDF
    // ──────────────────────────────────────

    public function download(SuratUndangan $suratUndangan)
    {
        $data = $suratUndangan->toArray();

        $data['tujuan_surat_list'] = json_decode($suratUndangan->tujuan_surat, true) ?? [];
        $data['lampiran_table']    = json_decode($suratUndangan->lampiran_table, true) ?? [];
        $data['use_lampiran']      = (bool) ($suratUndangan->use_lampiran ?? false);

        $is_pdf = true;

        $pdf = Pdf::loadView('admin.surat-undangan.template_pdf', compact('data', 'is_pdf'))
            ->setPaper('a4', 'portrait');

        $fileName = 'Undangan-' . str_replace('/', '-', $suratUndangan->nomor_surat) . '.pdf';

        return $pdf->stream($fileName);
    }

    // ──────────────────────────────────────
    // DESTROY
    // ──────────────────────────────────────

    public function destroy(SuratUndangan $suratUndangan)
    {
        $suratUndangan->delete();

        return back()->with('success', 'Arsip surat berhasil dihapus.');
    }

    // ──────────────────────────────────────
    // HELPER: PROCESS DATA
    // ──────────────────────────────────────

    private function processData(Request $request): array
    {
        $data = $request->all();

        // 1. Format tanggal acara → "Senin, 05 Mei 2025"
        Carbon::setLocale('id');
        $data['hari_tanggal_acara'] = $request->filled('tanggal_acara_raw')
            ? Carbon::parse($request->tanggal_acara_raw)->translatedFormat('l, d F Y')
            : '-';

        // 2. Logic tujuan & lampiran
        $mode                = $request->input('mode_tujuan', 'biasa');
        $data['use_lampiran'] = ($mode === 'lampiran');

        if ($data['use_lampiran']) {
            $data['tujuan_surat_list'] = ['Daftar Terlampir'];

            $rawContent    = $request->input('lampiran_content', '');
            $rawLines      = explode("\n", $rawContent);
            $parsedLampiran = [];

            foreach ($rawLines as $line) {
                if (trim($line) !== '') {
                    $parts            = explode('-', $line, 2);
                    $parsedLampiran[] = [
                        'nama'    => trim($parts[0]),
                        'jabatan' => isset($parts[1]) ? trim($parts[1]) : '-',
                    ];
                }
            }

            $data['lampiran_table']   = $parsedLampiran;
            $data['lampiran_content'] = $rawContent;
        } else {
            $data['tujuan_surat_list'] = $request->has('tujuan_surat') && is_array($request->tujuan_surat)
                ? $request->tujuan_surat
                : [];
            $data['lampiran_table']    = [];
            $data['lampiran_content']  = null;
        }

        // 3. Penandatangan
        $signerIndex = (int) $request->input('penandatangan_index', 0);
        $signer      = $this->penandatangans[$signerIndex] ?? $this->penandatangans[0];

        $data['jabatan_penandatangan'] = $signer['jabatan'];
        $data['nama_penandatangan']    = $signer['nama'];
        $data['nip_penandatangan']     = $signer['nip'];
        $data['ttd_image']             = ($request->boolean('use_ttd') && !empty($signer['ttd_image']))
            ? $signer['ttd_image']
            : null;

        return $data;
    }
}