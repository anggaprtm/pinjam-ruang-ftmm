<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyRuanganRequest;
use App\Http\Requests\StoreRuanganRequest;
use App\Http\Requests\UpdateRuanganRequest;
use App\Models\Ruangan;
use App\Models\Kegiatan; // Tambahkan ini
use App\Models\JadwalPerkuliahan;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;

class RuanganController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('ruangan_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $ruangan = Ruangan::all();
        // $ruangan = Ruangan::orderBy('id', 'asc')->get();

        return view('admin.ruangan.index', compact('ruangan'));
    }

    public function create()
    {
        abort_if(Gate::denies('ruangan_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.ruangan.create');
    }

    public function store(StoreRuanganRequest $request)
    {
        $ruangan = Ruangan::create($request->all());

        return redirect()->route('admin.ruangan.index')->with('success','Ruangan berhasil ditambahkan!');
    }

    public function edit(Ruangan $ruangan)
    {
        abort_if(Gate::denies('ruangan_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.ruangan.edit', compact('ruangan'));
    }

    public function update(UpdateRuanganRequest $request, Ruangan $ruangan)
    {
        $ruangan->update($request->all());

        return redirect()->route('admin.ruangan.index');
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
            $events[] = [
                'title' => $kegiatan->nama_kegiatan,
                'start' => $kegiatan->waktu_mulai,
                'end'   => $kegiatan->waktu_selesai,
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

                $events[] = [
                    'title' => $jadwal->nama_mk,
                    'start' => $date->toDateString() . ' ' . $jadwal->jam_mulai,
                    'end'   => $date->toDateString() . ' ' . $jadwal->jam_selesai,
                    'color' => '#17a2b8', // Warna untuk perkuliahan
                ];
            }
        }

        return view('admin.ruangan.show', compact('ruangan', 'events'));
    }

    public function destroy(Ruangan $ruangan)
    {
        abort_if(Gate::denies('ruangan_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $ruangan->delete();

        return back();
    }

    public function massDestroy(MassDestroyRuanganRequest $request)
    {
        $ruangan = Ruangan::find(request('ids'));

        foreach ($ruangan as $ruangan) {
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
