<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBarangRequest;
use App\Http\Requests\UpdateBarangRequest;
use App\Models\Barang;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Storage;

class BarangController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('barang_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $barangs = Barang::all();

        $barangsDipinjam = \Illuminate\Support\Facades\DB::table('barang_kegiatan')
            ->join('barangs', 'barang_kegiatan.barang_id', '=', 'barangs.id')
            ->join('kegiatan', 'barang_kegiatan.kegiatan_id', '=', 'kegiatan.id')
            ->join('users', 'kegiatan.user_id', '=', 'users.id')
            ->where('barang_kegiatan.status', 'dipinjam')
            ->select(
                'barangs.nama_barang as nama_barang',
                'kegiatan.nama_kegiatan',
                'kegiatan.id as kegiatan_id',
                'users.name as nama_peminjam',
                'barang_kegiatan.jumlah'
            )
            ->get();

        return view('admin.barangs.index', compact('barangs', 'barangsDipinjam'));
    }

    public function create()
    {
        abort_if(Gate::denies('barang_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.barangs.create');
    }

    public function store(StoreBarangRequest $request)
    {
        $data = $request->all();

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('uploads/barangs', 'public');
        }

        Barang::create($data);

        return redirect()->route('admin.barangs.index')->with('success', 'Barang berhasil ditambahkan.');
    }

    public function show(Barang $barang)
    {
        abort_if(Gate::denies('barang_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.barangs.show', compact('barang'));
    }

    public function edit(Barang $barang)
    {
        abort_if(Gate::denies('barang_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.barangs.edit', compact('barang'));
    }

    public function update(UpdateBarangRequest $request, Barang $barang)
    {
        $data = $request->all();

        if ($request->hasFile('foto')) {
            if ($barang->foto && Storage::disk('public')->exists($barang->foto)) {
                Storage::disk('public')->delete($barang->foto);
            }
            $data['foto'] = $request->file('foto')->store('uploads/barangs', 'public');
        }

        $barang->update($data);

        return redirect()->route('admin.barangs.index')->with('success', 'Barang berhasil diperbarui.');
    }

    public function destroy(Barang $barang)
    {
        abort_if(Gate::denies('barang_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if ($barang->foto && Storage::disk('public')->exists($barang->foto)) {
            Storage::disk('public')->delete($barang->foto);
        }

        $barang->delete();

        return back()->with('success', 'Barang berhasil dihapus.');
    }
}
