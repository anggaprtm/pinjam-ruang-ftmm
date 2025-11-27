<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyRuanganRequest;
use App\Http\Requests\StoreRuanganRequest;
use App\Http\Requests\UpdateRuanganRequest;
use App\Models\Ruangan;
use App\Models\Kegiatan; 
use App\Models\JadwalPerkuliahan;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RuanganController extends Controller
{
    public function index(Request $request)
    {
        // Ini adalah bagian yang menangani permintaan DataTables (AJAX)
        if ($request->ajax()) {
            $query = Ruangan::query()->select(sprintf('%s.*', (new Ruangan())->table));
            $table = Datatables::of($query);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {
                $viewGate      = 'ruangan_show';
                $editGate      = 'ruangan_edit';
                $deleteGate    = 'ruangan_delete';
                $crudRoutePart = 'ruangan';

                return view('partials.datatablesActions', compact(
                    'viewGate',
                    'editGate',
                    'deleteGate',
                    'crudRoutePart',
                    'row'
                ));
            });

            $table->editColumn('id', function ($row) {
                return $row->id ? $row->id : '';
            });
            $table->editColumn('nama', function ($row) {
                return $row->nama ? $row->nama : '';
            });
            $table->editColumn('kapasitas', function ($row) {
                return $row->kapasitas ? $row->kapasitas : '';
            });

            $table->rawColumns(['actions', 'placeholder']);

            return $table->make(true);
        }

        // === PERBAIKAN DI SINI ===
        // Saat halaman dimuat pertama kali (bukan AJAX),
        // ambil semua data ruangan dan kirim ke view.
        $ruangan = Ruangan::all();
        return view('admin.ruangan.index', compact('ruangan'));
    }

    public function create()
    {
        abort_if(Gate::denies('ruangan_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.ruangan.create');
    }

    public function store(StoreRuanganRequest $request)
    {
        $data = $request->all();
        
        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('uploads/fotos', 'public');
        }

        Ruangan::create($data);

        return redirect()->route('admin.ruangan.index');
    }

    public function edit(Ruangan $ruangan)
    {
        abort_if(Gate::denies('ruangan_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.ruangan.edit', compact('ruangan'));
    }

    public function update(UpdateRuanganRequest $request, Ruangan $ruangan)
    {
        $data = $request->all();

        if ($request->hasFile('foto')) {
            if ($ruangan->foto && Storage::disk('public')->exists($ruangan->foto)) {
                Storage::disk('public')->delete($ruangan->foto);
            }
            $data['foto'] = $request->file('foto')->store('uploads/fotos', 'public');
        }

        $ruangan->update($data);

        return redirect()
            ->route('admin.ruangan.index')
            ->with('success', 'Data ruangan berhasil diperbarui!');
    }

    public function show(Ruangan $ruangan)
    {
        abort_if(Gate::denies('ruangan_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $events = [];

        // 1. Ambil Kegiatan Umum untuk ruangan ini
        $kegiatans = Kegiatan::where('ruangan_id', $ruangan->id)
            ->where('status', 'disetujui')
            ->get();

        foreach ($kegiatans as $kegiatan) {
            // Ambil nilai asli dari atribut waktu (format Y-m-d H:i:s di DB) untuk menghindari formatting accessor
            $rawStart = $kegiatan->getOriginal('waktu_mulai') ?? $kegiatan->waktu_mulai;
            $rawEnd = $kegiatan->getOriginal('waktu_selesai') ?? $kegiatan->waktu_selesai;

            $events[] = [
                'title' => $kegiatan->nama_kegiatan,
                // Kirim sebagai ISO 8601 dengan offset timezone untuk menghindari pergeseran tanggal di client
                'start' => Carbon::parse($rawStart)->toIso8601String(),
                'end'   => Carbon::parse($rawEnd)->toIso8601String(),
                'color' => '#741847', // Warna untuk kegiatan umum
            ];
        }

        // 2. Ambil Jadwal Perkuliahan untuk ruangan ini
        $jadwals = JadwalPerkuliahan::where('ruangan_id', $ruangan->id)->get();

        foreach ($jadwals as $jadwal) {
            try {
                $startDate = Carbon::parse($jadwal->berlaku_mulai);
                $endDate = Carbon::parse($jadwal->berlaku_sampai);
            } catch (\Exception $e) { continue; }

            $targetDay = strtolower($jadwal->hari);

            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                if (strtolower($date->locale('id')->isoFormat('dddd')) !== $targetDay) {
                    continue;
                }

                // Gabungkan tanggal dan jam, lalu kirim sebagai local ISO (YYYY-MM-DDTHH:MM:SS)
                // Gunakan field yang benar: mata_kuliah, waktu_mulai, waktu_selesai
                try {
                    $startDT = Carbon::parse($date->toDateString() . ' ' . $jadwal->waktu_mulai)->toIso8601String();
                    $endDT = Carbon::parse($date->toDateString() . ' ' . $jadwal->waktu_selesai)->toIso8601String();
                } catch (\Exception $e) {
                    $startDT = Carbon::parse($date->toDateString() . ' ' . ($jadwal->waktu_mulai ?? '00:00:00'))->toIso8601String();
                    $endDT = Carbon::parse($date->toDateString() . ' ' . ($jadwal->waktu_selesai ?? '23:59:59'))->toIso8601String();
                }

                $events[] = [
                    'title' => $jadwal->mata_kuliah,
                    'start' => $startDT,
                    'end'   => $endDT,
                    'color' => '#17a2b8', // Warna untuk perkuliahan
                ];
            }
        }

        // Log events payload for debugging (user requested tracing)
        try {
            Log::info('RuanganController::show events payload for ruangan_id=' . $ruangan->id . ' -> ' . json_encode($events));
        } catch (\Exception $e) {
            // ignore logging failure
        }

        return view('admin.ruangan.show', compact('ruangan', 'events'));
    }

    public function destroy(Ruangan $ruangan)
    {
        abort_if(Gate::denies('ruangan_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        
        // Hapus file foto jika ada sebelum menghapus data dari database
        if ($ruangan->foto && Storage::disk('public')->exists($ruangan->foto)) {
            Storage::disk('public')->delete($ruangan->foto);
        }

        $ruangan->delete();

        return back();
    }

    public function massDestroy(MassDestroyRuanganRequest $request)
    {
        $ruangan = Ruangan::whereIn('id', request('ids'))->get();

        foreach ($ruangan as $ruangan) {
            // Hapus file foto untuk setiap ruangan yang akan dihapus
            if ($ruangan->foto && Storage::disk('public')->exists($ruangan->foto)) {
                Storage::disk('public')->delete($ruangan->foto);
            }
            $ruangan->delete();
        }

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function toggle($id)
    {
        $ruangan = Ruangan::findOrFail($id);
        $ruangan->is_active = !$ruangan->is_active;
        $ruangan->save();

        return back()->with('success', 'Status ruangan berhasil diubah!');
    }
}