<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SuratTugas;
use App\Models\User;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class SuratTugasController extends Controller
{
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

    private array $tempatPresets = [
        'Universitas Airlangga Surabaya',
        'Kampus C Universitas Airlangga, Surabaya',
        'Gedung Nano Kampus C Universitas Airlangga',
        'Jakarta',
        'Yogyakarta',
        'Zoom Meeting (Daring)',
    ];

    private array $dasarPresets = [
        'Rencana Kerja Fakultas Teknologi Maju dan Multidisiplin Tahun 2025',
        'Program Kerja Bidang Akademik dan Kemahasiswaan FTMM Tahun 2025',
        'Undangan dari penyelenggara kegiatan',
        'Kebutuhan pelaksanaan tugas dan fungsi jabatan',
    ];

    // ── INDEX ──────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = SuratTugas::query()->latest();

            return datatables()->of($query)
                ->addIndexColumn()
                ->addColumn('actions', function ($row) {
                    $b = '';

                    // Tombol nomor surat: merah jika belum ada, abu-abu jika sudah
                    if (empty($row->nomor_surat)) {
                        $b .= '<button type="button" class="btn btn-xs btn-danger me-1"
                            onclick="openNomorModal(' . $row->id . ', \'\')"
                            title="Belum bernomor — klik untuk isi nomor">
                            <i class="fas fa-hashtag"></i>
                        </button>';
                    } else {
                        $b .= '<button type="button" class="btn btn-xs btn-outline-secondary me-1"
                            onclick="openNomorModal(' . $row->id . ', \'' . addslashes($row->nomor_surat) . '\')"
                            title="Ubah nomor surat">
                            <i class="fas fa-hashtag"></i>
                        </button>';
                    }

                    // Download hanya jika sudah bernomor
                    if (!empty($row->nomor_surat)) {
                        $b .= '<a class="btn btn-xs btn-warning me-1"
                            href="' . route('admin.surat-tugas.download', $row->id) . '"
                            target="_blank" title="Download PDF">
                            <i class="fas fa-print"></i>
                        </a>';
                    }

                    $b .= '<a class="btn btn-sm btn-info me-1"
                        href="' . route('admin.surat-tugas.edit', $row->id) . '"
                        title="Edit"><i class="fas fa-edit text-white"></i></a>';

                    // PERUBAHAN DI SINI: Gunakan class form-delete dan hapus onsubmit bawaan
                    $b .= '<form action="' . route('admin.surat-tugas.destroy', $row->id) . '"
                        method="POST" class="d-inline form-delete">
                        <input type="hidden" name="_method" value="DELETE">
                        <input type="hidden" name="_token" value="' . csrf_token() . '">
                        <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </form>';

                    return $b;
                })
                ->editColumn('nomor_surat', function ($row) {
                    if (empty($row->nomor_surat)) {
                        return '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle px-2 py-1 small">
                                    <i class="fas fa-clock me-1"></i>Menunggu Nomor
                                </span>';
                    }
                    return '<div class="fw-semibold text-primary small">' . htmlspecialchars($row->nomor_surat) . '</div>'
                         . '<div class="text-muted" style="font-size:.75rem">' . htmlspecialchars($row->hal_surat ?? 'Surat Tugas') . '</div>';
                })
                ->editColumn('tanggal_surat', fn($row) =>
                    $row->tanggal_surat
                        ? '<div class="small">' . Carbon::parse($row->tanggal_surat)->translatedFormat('d M Y') . '</div>'
                          . '<div class="text-muted" style="font-size:.75rem">' . ($row->hari_tanggal_tugas ?? '-') . '</div>'
                        : '-'
                )
                ->editColumn('pegawai_list', function ($row) {
                    $list  = json_decode($row->pegawai_list, true) ?? [];
                    $count = count($list);
                    if ($count === 0) return '<span class="text-muted small">-</span>';
                    $first = htmlspecialchars($list[0]['nama'] ?? '-');
                    return $count > 1
                        ? $first . '<br><span class="badge bg-secondary" style="font-size:.7rem">+' . ($count - 1) . ' lainnya</span>'
                        : $first;
                })
                ->editColumn('isi_tugas', fn($row) =>
                    '<span class="small" title="' . htmlspecialchars($row->isi_tugas ?? '') . '">'
                    . htmlspecialchars(\Str::limit($row->isi_tugas ?? '-', 55)) . '</span>'
                )
                ->editColumn('nama_penandatangan', fn($row) =>
                    '<div class="small fw-semibold">' . htmlspecialchars($row->nama_penandatangan) . '</div>'
                    . '<div class="text-muted" style="font-size:.75rem">' . htmlspecialchars($row->jabatan_penandatangan) . '</div>'
                )
                ->rawColumns(['actions', 'nomor_surat', 'tanggal_surat', 'pegawai_list', 'isi_tugas', 'nama_penandatangan'])
                ->make(true);
        }

        return view('admin.surat-tugas.index');
    }

    // ── UPDATE NOMOR (AJAX) ────────────────────────────────────────────────

    public function updateNomor(Request $request, SuratTugas $suratTugas)
    {
        $request->validate(['nomor_surat' => ['required', 'string', 'max:255']]);
        $suratTugas->update(['nomor_surat' => $request->nomor_surat]);
        return response()->json(['success' => true, 'nomor_surat' => $request->nomor_surat]);
    }

    // ── CREATE ─────────────────────────────────────────────────────────────

    public function create()
    {
        return view('admin.surat-tugas.create', [
            'penandatangans' => $this->penandatangans,
            'tempatPresets'  => $this->tempatPresets,
            'dasarPresets'   => $this->dasarPresets,
            'pegawais'       => $this->getPegawais(),
        ]);
    }

    // ── STORE ──────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $request->validate([
            'nomor_surat'       => ['nullable', 'string', 'max:255'],
            'tanggal_surat'     => ['required', 'date'],
            'isi_tugas'         => ['required', 'string'],
            'tanggal_tugas_raw' => ['required', 'date'],
            'waktu_tugas'       => ['required', 'string', 'max:100'],
            'tempat_tugas'      => ['required', 'string', 'max:255'],
            'pegawai_nama.*'    => ['required', 'string'],
        ]);

        $data   = $this->processData($request);
        $record = SuratTugas::create($this->buildPayload($data, $request));

        if (!empty($record->nomor_surat)) {
            return redirect()->route('admin.surat-tugas.download', $record->id);
        }

        return redirect()->route('admin.surat-tugas.index')
            ->with('success', 'Surat tugas berhasil disimpan. Tambahkan nomor surat setelah mendapat dari e-office.');
    }

    // ── EDIT ───────────────────────────────────────────────────────────────

    public function edit(SuratTugas $suratTugas)
    {
        $suratTugas->pegawai_array = json_decode($suratTugas->pegawai_list, true) ?? [];

        return view('admin.surat-tugas.edit', [
            'surat'          => $suratTugas,
            'penandatangans' => $this->penandatangans,
            'tempatPresets'  => $this->tempatPresets,
            'dasarPresets'   => $this->dasarPresets,
            'pegawais'       => $this->getPegawais(),
        ]);
    }

    // ── UPDATE ─────────────────────────────────────────────────────────────

    public function update(Request $request, SuratTugas $suratTugas)
    {
        $request->validate([
            'nomor_surat'       => ['nullable', 'string', 'max:255'],
            'tanggal_surat'     => ['required', 'date'],
            'isi_tugas'         => ['required', 'string'],
            'tanggal_tugas_raw' => ['required', 'date'],
            'waktu_tugas'       => ['required', 'string', 'max:100'],
            'tempat_tugas'      => ['required', 'string', 'max:255'],
            'pegawai_nama.*'    => ['required', 'string'],
        ]);

        $data = $this->processData($request);
        $suratTugas->update($this->buildPayload($data, $request));

        $fresh = $suratTugas->fresh();
        if (!empty($fresh->nomor_surat)) {
            return redirect()->route('admin.surat-tugas.download', $suratTugas->id);
        }

        return redirect()->route('admin.surat-tugas.index')
            ->with('success', 'Surat tugas berhasil diperbarui.');
    }

    // ── PREVIEW (AJAX) ─────────────────────────────────────────────────────

    public function preview(Request $request)
    {
        $data   = $this->processData($request);
        $is_pdf = false;

        return view('admin.surat-tugas.template_pdf', compact('data', 'is_pdf'))->render();
    }

    // ── DOWNLOAD PDF ───────────────────────────────────────────────────────

    public function download(SuratTugas $suratTugas)
    {
        if (empty($suratTugas->nomor_surat)) {
            return back()->with('error', 'Surat belum memiliki nomor resmi. Silakan isi nomor surat terlebih dahulu.');
        }

        $data                 = $suratTugas->toArray();
        $data['pegawai_list'] = json_decode($suratTugas->pegawai_list, true) ?? [];
        $is_pdf               = true;

        $pdf = Pdf::loadView('admin.surat-tugas.template_pdf', compact('data', 'is_pdf'))
            ->setPaper('a4', 'portrait');

        $fileName = 'SuratTugas-' . str_replace('/', '-', $suratTugas->nomor_surat) . '.pdf';

        return $pdf->stream($fileName);
    }

    // ── DESTROY ────────────────────────────────────────────────────────────

    public function destroy(SuratTugas $suratTugas)
    {
        $suratTugas->delete();

        return back()->with('success', 'Arsip surat tugas berhasil dihapus.');
    }

    // ── PRIVATE HELPERS ────────────────────────────────────────────────────

    private function getPegawais()
    {
        return User::with(['tendikDetail', 'dosenDetail'])
            ->whereHas('roles', fn($q) => $q->whereIn('title', ['Pegawai', 'Dosen']))
            ->whereNotNull('nip')
            ->orderBy('name')
            ->get();
    }

    private function buildPayload(array $data, Request $request): array
    {
        return [
            'nomor_surat'             => !empty($data['nomor_surat']) ? $data['nomor_surat'] : null,
            'tanggal_surat'           => $data['tanggal_surat'],
            'hal_surat'               => $data['hal_surat'] ?? 'Surat Tugas',
            'dasar_surat'             => $data['dasar_surat'] ?? null,
            'isi_tugas'               => $data['isi_tugas'],
            'hari_tanggal_tugas'      => $data['hari_tanggal_tugas'],
            'waktu_tugas'             => $request->waktu_tugas,
            'tanggal_tugas_raw'       => $data['tanggal_tugas_raw'],
            'tanggal_tugas_akhir_raw' => $data['tanggal_tugas_akhir_raw'] ?? null,
            'tempat_tugas'            => $data['tempat_tugas'],
            'pakaian'                 => $request->pakaian ?? null,
            'keterangan'              => $request->keterangan ?? null,
            'pegawai_list'            => json_encode($data['pegawai_list']),
            'jabatan_penandatangan'   => $data['jabatan_penandatangan'],
            'nama_penandatangan'      => $data['nama_penandatangan'],
            'nip_penandatangan'       => $data['nip_penandatangan'],
        ];
    }

    private function processData(Request $request): array
    {
        $data = $request->all();

        Carbon::setLocale('id');

        $tglMulai = $request->filled('tanggal_tugas_raw')
            ? Carbon::parse($request->tanggal_tugas_raw) : null;
        $tglAkhir = $request->filled('tanggal_tugas_akhir_raw')
            ? Carbon::parse($request->tanggal_tugas_akhir_raw) : null;

        if ($tglMulai && $tglAkhir && $tglMulai->ne($tglAkhir)) {
            if ($tglMulai->format('Y-m') === $tglAkhir->format('Y-m')) {
                $data['hari_tanggal_tugas'] = $tglMulai->translatedFormat('l')
                    . ' s.d. ' . $tglAkhir->translatedFormat('l')
                    . ', ' . $tglMulai->translatedFormat('d')
                    . ' s.d. ' . $tglAkhir->translatedFormat('j F Y');
            } else {
                $data['hari_tanggal_tugas'] = $tglMulai->translatedFormat('l, j F Y')
                    . ' s.d. ' . $tglAkhir->translatedFormat('l, j F Y');
            }
        } elseif ($tglMulai) {
            $data['hari_tanggal_tugas'] = $tglMulai->translatedFormat('l, j F Y');
        } else {
            $data['hari_tanggal_tugas'] = '-';
        }

        $names    = $request->input('pegawai_nama', []);
        $nipniks  = $request->input('pegawai_nip', []);
        $jabatans = $request->input('pegawai_jabatan', []);

        $pegawaiList = [];
        foreach ($names as $i => $nama) {
            if (!empty(trim($nama))) {
                $pegawaiList[] = [
                    'nama'    => trim($nama),
                    'nip'     => trim($nipniks[$i] ?? ''),
                    'jabatan' => trim($jabatans[$i] ?? ''),
                ];
            }
        }
        $data['pegawai_list'] = $pegawaiList;

        $signerIndex                   = (int) $request->input('penandatangan_index', 0);
        $signer                        = $this->penandatangans[$signerIndex] ?? $this->penandatangans[0];
        $data['jabatan_penandatangan'] = $signer['jabatan'];
        $data['nama_penandatangan']    = $signer['nama'];
        $data['nip_penandatangan']     = $signer['nip'];
        $data['ttd_image']             = ($request->boolean('use_ttd') && !empty($signer['ttd_image']))
            ? $signer['ttd_image'] : null;

        return $data;
    }
}