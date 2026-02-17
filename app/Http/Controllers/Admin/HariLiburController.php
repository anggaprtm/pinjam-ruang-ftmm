<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HariLibur;
use Illuminate\Http\Request;

class HariLiburController extends Controller
{
    public function index()
    {
        // Tampilkan data libur diurutkan dari yang terbaru
        $liburs = HariLibur::orderBy('tanggal', 'desc')->get();
        return view('admin.hari-libur.index', compact('liburs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal'    => 'required|date|unique:hari_liburs,tanggal',
            'keterangan' => 'required|string|max:255',
        ], [
            'tanggal.unique' => 'Tanggal ini sudah didaftarkan sebagai hari libur.'
        ]);

        HariLibur::create([
            'tanggal'    => $request->tanggal,
            'keterangan' => $request->keterangan,
        ]);

        return back()->with('success', 'Hari Libur berhasil ditambahkan!');
    }

    public function destroy(HariLibur $hari_libur)
    {
        $hari_libur->delete();
        return back()->with('success', 'Hari Libur berhasil dihapus!');
    }
}