<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyAsetFakultasRequest;
use App\Http\Requests\StoreAsetFakultasRequest;
use App\Http\Requests\UpdateAsetFakultasRequest;
use App\Imports\AsetFakultasImport;
use App\Models\AsetFakultas;
use App\Models\Ruangan;
use Gate;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use ZipArchive;

class AsetFakultasController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('aset_fakultas_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $ruanganList   = Ruangan::orderBy('nama')->get();
        $filterRuangan = $request->get('ruangan_id');
        $filterKondisi = $request->get('kondisi');
        $filterSearch  = $request->get('search');

        $query = AsetFakultas::with('ruangan')->orderBy('nama_barang');

        if ($filterRuangan) {
            $query->where('ruangan_id', $filterRuangan);
        }
        if ($filterKondisi) {
            $query->where('kondisi', $filterKondisi);
        }
        if ($filterSearch) {
            $query->where(function ($q) use ($filterSearch) {
                $q->where('nama_barang', 'like', "%{$filterSearch}%")
                  ->orWhere('kode_barang', 'like', "%{$filterSearch}%")
                  ->orWhere('merk', 'like', "%{$filterSearch}%");
            });
        }

        $asets          = $query->paginate(50)->withQueryString();
        $kondisiOptions = AsetFakultas::KONDISI_OPTIONS;

        $stats = [
            'total'        => AsetFakultas::count(),
            'baik'         => AsetFakultas::where('kondisi', 'Baik')->count(),
            'rusak_ringan' => AsetFakultas::where('kondisi', 'Rusak Ringan')->count(),
            'rusak_berat'  => AsetFakultas::where('kondisi', 'Rusak Berat')->count(),
        ];

        // Hitung jumlah aset per ruangan untuk modal export
        $asetPerRuangan = AsetFakultas::whereNotNull('ruangan_id')
            ->selectRaw('ruangan_id, count(*) as total')
            ->groupBy('ruangan_id')
            ->pluck('total', 'ruangan_id');

        return view('admin.aset-fakultas.index', compact(
            'asets', 'ruanganList', 'kondisiOptions', 'stats',
            'filterRuangan', 'filterKondisi', 'filterSearch', 'asetPerRuangan'
        ));
    }

    public function create()
    {
        abort_if(Gate::denies('aset_fakultas_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $ruanganList    = Ruangan::orderBy('nama')->pluck('nama', 'id');
        $kondisiOptions = AsetFakultas::KONDISI_OPTIONS;

        return view('admin.aset-fakultas.create', compact('ruanganList', 'kondisiOptions'));
    }

    public function store(StoreAsetFakultasRequest $request)
    {
        AsetFakultas::create($request->validated());

        return redirect()->route('admin.aset-fakultas.index')
            ->with('success', 'Aset berhasil ditambahkan!');
    }

    public function show(AsetFakultas $asetFakultas)
    {
        abort_if(Gate::denies('aset_fakultas_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $asetFakultas->load('ruangan');

        return view('admin.aset-fakultas.show', compact('asetFakultas'));
    }

    public function edit(AsetFakultas $asetFakultas)
    {
        abort_if(Gate::denies('aset_fakultas_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $ruanganList    = Ruangan::orderBy('nama')->pluck('nama', 'id');
        $kondisiOptions = AsetFakultas::KONDISI_OPTIONS;

        return view('admin.aset-fakultas.edit', compact('asetFakultas', 'ruanganList', 'kondisiOptions'));
    }

    public function update(UpdateAsetFakultasRequest $request, AsetFakultas $asetFakultas)
    {
        $asetFakultas->update($request->validated());

        return redirect()->route('admin.aset-fakultas.index')
            ->with('success', 'Data aset berhasil diperbarui!');
    }

    public function destroy(AsetFakultas $asetFakultas)
    {
        abort_if(Gate::denies('aset_fakultas_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $asetFakultas->delete();

        return back()->with('success', 'Aset berhasil dihapus!');
    }

    public function massDestroy(MassDestroyAsetFakultasRequest $request)
    {
        AsetFakultas::whereIn('id', $request->ids)->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    // ──────────────────────────────────────
    // IMPORT
    // ──────────────────────────────────────

    public function importForm()
    {
        abort_if(Gate::denies('aset_fakultas_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.aset-fakultas.import');
    }

    public function import(Request $request)
    {
        abort_if(Gate::denies('aset_fakultas_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
        ]);

        try {
            $import = new AsetFakultasImport();
            Excel::import($import, $request->file('file'));

            $errors = $import->errors();

            if ($errors->count() > 0) {
                $errorMessages = $errors->map(fn($e) => $e->getMessage())->implode('; ');
                return redirect()->route('admin.aset-fakultas.index')
                    ->with('warning', "Import selesai dengan beberapa baris yang dilewati: {$errorMessages}");
            }

            return redirect()->route('admin.aset-fakultas.index')
                ->with('success', 'Import data aset berhasil!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    // ──────────────────────────────────────
    // HELPER: GROUPING DATA ASET
    // ──────────────────────────────────────
    private function getGroupedAsets($ruanganId)
    {
        $asetsRaw = AsetFakultas::where('ruangan_id', $ruanganId)->orderBy('nama_barang')->get();

        return $asetsRaw->groupBy(function ($item) {
            return $item->nama_barang . '|' . ($item->merk ?? '-') . '|' . ($item->tahun_aset ?? '-') . '|' . ($item->anggaran ?? 'DAMAS');
        })->map(function ($group) {
            return (object) [
                'nama_barang' => $group->first()->nama_barang,
                'merk'        => $group->first()->merk,
                'tahun_aset'  => $group->first()->tahun_aset,
                'anggaran'    => $group->first()->anggaran,
                'jumlah'      => $group->count(),
                'kode_barang' => $group->pluck('kode_barang')->implode('; '),
                'kondisi'     => $group->pluck('kondisi')->unique()->implode(', ')
            ];
        })->values();
    }

    // ──────────────────────────────────────
    // EXPORT PDF — Single Ruangan
    // ──────────────────────────────────────

    public function exportPdf(Request $request)
    {
        abort_if(Gate::denies('aset_fakultas_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $request->validate([
            'ruangan_id'   => ['required', 'exists:ruangan,id'],
            'tanggal_ttd'  => ['nullable', 'date'],
        ]);

        $ruangan      = Ruangan::findOrFail($request->ruangan_id);
        $groupedAsets = $this->getGroupedAsets($ruangan->id);
        $tanggalTtd   = $request->tanggal_ttd ? \Carbon\Carbon::parse($request->tanggal_ttd) : now();

        $pdf = Pdf::loadView('admin.aset-fakultas.pdf.dir', [
            'ruangan'    => $ruangan, 
            'asets'      => $groupedAsets, 
            'tanggalTtd' => $tanggalTtd
        ])->setPaper('a4', 'portrait');

        $filename = 'DIR_' . \Str::slug($ruangan->nama) . '_' . now()->format('Ymd') . '.pdf';

        return $pdf->download($filename);
    }

    // ──────────────────────────────────────
    // EXPORT ZIP — Multiple Ruangan
    // ──────────────────────────────────────

    public function exportZip(Request $request)
    {
        abort_if(Gate::denies('aset_fakultas_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $request->validate([
            'ruangan_ids'   => ['required', 'array'],
            'ruangan_ids.*' => ['exists:ruangan,id'],
            'tanggal_ttd'   => ['nullable', 'date'],
        ]);

        $tanggalTtd = $request->tanggal_ttd ? \Carbon\Carbon::parse($request->tanggal_ttd) : now();

        // Jika hanya 1 ruangan → langsung download PDF
        if (count($request->ruangan_ids) === 1) {
            $ruangan      = Ruangan::findOrFail($request->ruangan_ids[0]);
            $groupedAsets = $this->getGroupedAsets($ruangan->id);
            
            $pdf = Pdf::loadView('admin.aset-fakultas.pdf.dir', [
                'ruangan'    => $ruangan, 
                'asets'      => $groupedAsets, 
                'tanggalTtd' => $tanggalTtd
            ])->setPaper('a4', 'portrait');
            
            $filename = 'DIR_' . \Str::slug($ruangan->nama) . '_' . now()->format('Ymd') . '.pdf';
            return $pdf->download($filename);
        }

        // Lebih dari 1 ruangan → buat ZIP
        $tmpDir  = storage_path('app/tmp/dir_export_' . uniqid());
        mkdir($tmpDir, 0755, true);
        $zipPath = $tmpDir . '/DIR_Batch_' . now()->format('Ymd') . '.zip';

        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE);

        foreach ($request->ruangan_ids as $ruanganId) {
            $ruangan = Ruangan::find($ruanganId);
            if (!$ruangan) continue;

            $groupedAsets = $this->getGroupedAsets($ruangan->id);

            $pdf = Pdf::loadView('admin.aset-fakultas.pdf.dir', [
                'ruangan'    => $ruangan, 
                'asets'      => $groupedAsets, 
                'tanggalTtd' => $tanggalTtd
            ])->setPaper('a4', 'portrait');
            
            $filename = 'DIR_' . \Str::slug($ruangan->nama) . '_' . now()->format('Ymd') . '.pdf';
            $pdfPath  = $tmpDir . '/' . $filename;

            file_put_contents($pdfPath, $pdf->output());
            $zip->addFile($pdfPath, $filename);
        }

        $zip->close();

        return response()->download($zipPath, 'DIR_Batch_' . now()->format('Ymd') . '.zip')
            ->deleteFileAfterSend(false);
    }
}