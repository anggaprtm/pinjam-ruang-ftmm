<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJadwalPerkuliahanRequest;
use App\Http\Requests\UpdateJadwalPerkuliahanRequest;
use App\Http\Requests\MassDestroyJadwalPerkuliahanRequest;
use App\Services\EventService;
use App\Models\Kegiatan;
use App\Models\JadwalPerkuliahan;
use App\Models\Ruangan;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\JadwalPerkuliahanImport;


class JadwalPerkuliahanController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('kuliah_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $jadwals = JadwalPerkuliahan::with('ruangan')->get();

        return view('admin.jadwal-perkuliahan.index', compact('jadwals'));
    }

    public function create()
    {
        abort_if(Gate::denies('kuliah_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $ruangan = Ruangan::pluck('nama', 'id')->prepend(trans('global.pleaseSelect'), '');

        return view('admin.jadwal-perkuliahan.create', compact('ruangan'));
    }

    public function store(StoreJadwalPerkuliahanRequest $request, EventService $eventService)
    {
        $bentrok = $eventService->isRoomTakenForLecture($request->all());

        if ($bentrok) {
            return redirect()->back()
                ->withInput($request->input())
                ->withErrors('Ruangan bentrok dengan: ' . $bentrok->mata_kuliah);
        }

        $bentrokKegiatan = $eventService->isRoomTakenByKegiatan($request->all());

        if ($bentrokKegiatan) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['Ruangan bentrok dengan kegiatan: ' . $bentrokKegiatan->nama_kegiatan]);
        }

        $data = $request->all();

        // Convert tanggal ke format MySQL
        $data['berlaku_mulai'] = \Carbon\Carbon::createFromFormat('j M Y', $data['berlaku_mulai'])->format('Y-m-d');
        $data['berlaku_sampai'] = \Carbon\Carbon::createFromFormat('j M Y', $data['berlaku_sampai'])->format('Y-m-d');

        JadwalPerkuliahan::create($data);

        return redirect()->route('admin.jadwal-perkuliahan.index')->with('success', 'Jadwal perkuliahan berhasil ditambahkan!');
    }

    public function edit(JadwalPerkuliahan $jadwalPerkuliahan)
    {
        abort_if(Gate::denies('kuliah_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $ruangan = Ruangan::pluck('nama', 'id')->prepend(trans('global.pleaseSelect'), '');

        return view('admin.jadwal-perkuliahan.edit', compact('jadwalPerkuliahan', 'ruangan'));
    }

    public function update(UpdateJadwalPerkuliahanRequest $request, JadwalPerkuliahan $jadwalPerkuliahan)
    {
        $data = $request->all();

        $data['berlaku_mulai'] = \Carbon\Carbon::createFromFormat('j M Y', $data['berlaku_mulai'])->format('Y-m-d');
        $data['berlaku_sampai'] = \Carbon\Carbon::createFromFormat('j M Y', $data['berlaku_sampai'])->format('Y-m-d');

        $jadwalPerkuliahan->update($data);

        return redirect()->route('admin.jadwal-perkuliahan.index');
    }

    public function show(JadwalPerkuliahan $jadwalPerkuliahan)
    {
        abort_if(Gate::denies('kuliah_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.jadwal-perkuliahan.show', compact('jadwalPerkuliahan'));
    }

    public function destroy(JadwalPerkuliahan $jadwalPerkuliahan)
    {
        abort_if(Gate::denies('kuliah_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $jadwalPerkuliahan->delete();

        return back();
    }

    public function massDestroy(MassDestroyJadwalPerkuliahanRequest $request)
    {
        JadwalPerkuliahan::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function import(Request $request)
    {
    $request->validate([
        'file' => 'required|file|mimes:xlsx,xls,csv'
    ]);

    Excel::import(new JadwalPerkuliahanImport, $request->file('file'));

    return redirect()->route('admin.jadwal-perkuliahan.index')->with('success', 'Data berhasil diimport!');
    }
}
