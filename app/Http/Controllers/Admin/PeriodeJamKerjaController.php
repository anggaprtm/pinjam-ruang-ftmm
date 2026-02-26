<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PeriodeJamKerja;
use Illuminate\Http\Request;

class PeriodeJamKerjaController extends Controller
{
    public function index()
    {
        $periodes = PeriodeJamKerja::orderBy('tanggal_mulai', 'desc')->get();
        return view('admin.periode-jam-kerja.index', compact('periodes'));
    }

    public function create()
    {
        return view('admin.periode-jam-kerja.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_periode' => 'required|string|max:255',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'jam_masuk' => 'required|date_format:H:i',
            'jam_pulang_senin_kamis' => 'required|date_format:H:i',
            'jam_pulang_jumat' => 'required|date_format:H:i',
        ]);

        PeriodeJamKerja::create($validated);

        return redirect()->route('admin.periode-jam-kerja.index')
            ->with('message', 'Periode jam kerja berhasil ditambahkan!');
    }

    public function edit(PeriodeJamKerja $periode_jam_kerja)
    {
        // Pakai variable name $periode biar lebih singkat di view
        $periode = $periode_jam_kerja;
        return view('admin.periode-jam-kerja.form', compact('periode'));
    }

    public function update(Request $request, PeriodeJamKerja $periode_jam_kerja)
    {
        $validated = $request->validate([
            'nama_periode' => 'required|string|max:255',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'jam_masuk' => 'required|date_format:H:i',
            'jam_pulang_senin_kamis' => 'required|date_format:H:i',
            'jam_pulang_jumat' => 'required|date_format:H:i',
        ]);

        $periode_jam_kerja->update($validated);

        return redirect()->route('admin.periode-jam-kerja.index')
            ->with('message', 'Periode jam kerja berhasil diperbarui!');
    }

    public function destroy(PeriodeJamKerja $periode_jam_kerja)
    {
        $periode_jam_kerja->delete();
        return redirect()->route('admin.periode-jam-kerja.index')
            ->with('message', 'Periode jam kerja berhasil dihapus!');
    }
}