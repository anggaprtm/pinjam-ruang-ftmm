<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJadwalPerkuliahanRequest;
use App\Http\Requests\UpdateJadwalPerkuliahanRequest;
use App\Http\Requests\MassDestroyJadwalPerkuliahanRequest;
use App\Services\EventService;
use App\Models\JadwalPerkuliahan;
use App\Models\Ruangan;
use App\Models\Semester; // Jangan lupa import ini
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\JadwalPerkuliahanImport;

class JadwalPerkuliahanController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('kuliah_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // 1. Ambil Input Filter
    $hari = $request->input('hari');
    $programStudi = $request->input('program_studi'); // Tambahan baru
    
    // Logic Semester (tetap sama)
    $activeSemester = Semester::active()->first();
    $semesterId = $request->input('semester_id', $activeSemester ? $activeSemester->id : Semester::latest()->value('id'));

    $query = JadwalPerkuliahan::with(['ruangan', 'semester']);

    // 2. Terapkan Filter
    if ($semesterId) {
        $query->where('semester_id', $semesterId);
    }
    
    if ($hari) {
        $query->where('hari', $hari);
    }

    if ($programStudi) { // Filter baru
        $query->where('program_studi', $programStudi);
    }

    $jadwals = $query->get();
    
    // Data pendukung view
    $semesters = Semester::orderBy('tanggal_mulai', 'desc')->pluck('nama', 'id');
    $currentSemester = Semester::find($semesterId);

    // List Prodi (Hardcode sesuai Enum di database/Request validation)
    $listProdi = ['TSD', 'TI', 'RN', 'TRKB', 'TE'];

    // Jangan lupa pass $programStudi dan $listProdi ke compact
    return view('admin.jadwal-perkuliahan.index', compact('jadwals', 'hari', 'semesters', 'semesterId', 'currentSemester', 'programStudi', 'listProdi'));
    }

    public function create()
    {
        abort_if(Gate::denies('kuliah_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $ruangan = Ruangan::pluck('nama', 'id')->prepend(trans('global.pleaseSelect'), '');
        
        // Kirim info semester aktif ke view
        $activeSemester = Semester::active()->first();

        return view('admin.jadwal-perkuliahan.create', compact('ruangan', 'activeSemester'));
    }

    public function store(StoreJadwalPerkuliahanRequest $request, EventService $eventService)
    {
        $activeSemester = Semester::active()->first();

        if (!$activeSemester) {
            return redirect()->back()->withInput()->withErrors(['msg' => 'Gagal: Belum ada Semester Aktif yang diatur oleh Admin.']);
        }

        $data = $request->all();
        // Inject Semester ID Otomatis
        $data['semester_id'] = $activeSemester->id;

        // Cek Bentrok Kuliah (Logic baru via semester_id)
        $bentrok = $eventService->isRoomTakenForLecture($data);

        if ($bentrok) {
            return redirect()->back()
                ->withInput()
                ->withErrors('Ruangan bentrok dengan Mata Kuliah: ' . $bentrok->mata_kuliah . ' (' . \Carbon\Carbon::parse($bentrok->waktu_mulai)->format('H:i') . '-' . \Carbon\Carbon::parse($bentrok->waktu_selesai)->format('H:i') . ')');
        }

        // Cek Bentrok Kegiatan (Logic baru via tanggal semester)
        $bentrokKegiatan = $eventService->isRoomTakenByKegiatan($data);

        if ($bentrokKegiatan) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['Ruangan bentrok dengan kegiatan: ' . $bentrokKegiatan->nama_kegiatan]);
        }

        JadwalPerkuliahan::create($data);

        return redirect()->route('admin.jadwal-perkuliahan.index')->with('success', 'Jadwal berhasil ditambahkan untuk ' . $activeSemester->nama);
    }

    public function edit(JadwalPerkuliahan $jadwalPerkuliahan)
    {
        abort_if(Gate::denies('kuliah_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $ruangan = Ruangan::pluck('nama', 'id')->prepend(trans('global.pleaseSelect'), '');
        
        // Kita perlu tahu jadwal ini milik semester mana
        $semester = $jadwalPerkuliahan->semester;

        return view('admin.jadwal-perkuliahan.edit', compact('jadwalPerkuliahan', 'ruangan', 'semester'));
    }

    public function update(UpdateJadwalPerkuliahanRequest $request, JadwalPerkuliahan $jadwalPerkuliahan, EventService $eventService)
    {
        $data = $request->all();
        
        // Pastikan semester_id tidak berubah saat edit, kecuali kita sediakan fitur pindah semester
        // Di sini kita kunci agar tetap di semester aslinya
        $data['semester_id'] = $jadwalPerkuliahan->semester_id;
        $data['id'] = $jadwalPerkuliahan->id; // Penting untuk exclude self check

        // Validasi Bentrok Ulang (Optional tapi Recommended)
        $bentrok = $eventService->isRoomTakenForLecture($data);
        if ($bentrok) {
             return redirect()->back()->withInput()->withErrors('Update Gagal: Bentrok dengan ' . $bentrok->mata_kuliah);
        }

        $jadwalPerkuliahan->update($data);

        return redirect()->route('admin.jadwal-perkuliahan.index')->with('success', 'Jadwal berhasil diperbarui');
    }

    public function show(JadwalPerkuliahan $jadwalPerkuliahan)
    {
        abort_if(Gate::denies('kuliah_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $jadwalPerkuliahan->load('semester', 'ruangan');

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

        // Note: Kamu perlu update logic ImportJadwalPerkuliahan juga 
        // agar otomatis set semester_id = Semester::active()->id
        Excel::import(new JadwalPerkuliahanImport, $request->file('file'));

        return redirect()->route('admin.jadwal-perkuliahan.index')->with('success', 'Data berhasil diimport!');
    }
}