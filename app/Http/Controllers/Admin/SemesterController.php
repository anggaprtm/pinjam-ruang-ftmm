<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Semester;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Gate;

class SemesterController extends Controller
{
    public function index()
    {
        // abort_if(Gate::denies('semester_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        // Note: Uncomment baris di atas jika sudah buat permission

        $semesters = Semester::orderBy('tanggal_mulai', 'desc')->get();

        return view('admin.semesters.index', compact('semesters'));
    }

    public function create()
    {
        return view('admin.semesters.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
        ]);

        $data = $request->all();
        
        // Logic: Jika user centang "Aktif", matikan semester lain
        if ($request->has('is_active') && $request->is_active == 1) {
            Semester::where('is_active', 1)->update(['is_active' => 0]);
        } else {
            // Jika ini semester pertama kali dibuat, paksa jadi aktif
            if (Semester::count() == 0) {
                $data['is_active'] = 1;
            } else {
                $data['is_active'] = 0;
            }
        }

        Semester::create($data);

        return redirect()->route('admin.semesters.index')->with('success', 'Semester berhasil ditambahkan');
    }

    public function edit(Semester $semester)
    {
        return view('admin.semesters.edit', compact('semester'));
    }

    public function update(Request $request, Semester $semester)
    {
        $request->validate([
            'nama' => 'required|string',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
        ]);

        $data = $request->all();

        // Handle Checkbox (kalau tidak dicentang, value-nya null, jadi set ke 0)
        $isActive = $request->has('is_active') ? 1 : 0;
        $data['is_active'] = $isActive;

        if ($isActive) {
            // Matikan yang lain
            Semester::where('id', '!=', $semester->id)->update(['is_active' => 0]);
        }

        $semester->update($data);

        return redirect()->route('admin.semesters.index')->with('success', 'Semester berhasil diperbarui');
    }

    public function destroy(Semester $semester)
    {
        // Cek relasi dulu biar aman
        if ($semester->jadwalPerkuliahan()->exists()) {
             return back()->withErrors('Gagal hapus: Semester ini memiliki data jadwal kuliah.');
        }

        $semester->delete();
        return back()->with('success', 'Semester dihapus');
    }
}