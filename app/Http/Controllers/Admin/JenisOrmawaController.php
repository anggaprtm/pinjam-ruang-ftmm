<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisOrmawa;
use Illuminate\Http\Request;

class JenisOrmawaController extends Controller
{
    public function index()
    {
        $this->authorizeMasterAccess();

        $items = JenisOrmawa::orderBy('nama_jenis')->paginate(20);

        return view('admin.ormawa-masters.jenis.index', compact('items'));
    }

    public function create()
    {
        $this->authorizeMasterAccess();

        return view('admin.ormawa-masters.jenis.create');
    }

    public function store(Request $request)
    {
        $this->authorizeMasterAccess();

        $data = $request->validate([
            'nama_jenis' => ['required', 'string', 'max:255'],
            'kode' => ['nullable', 'string', 'max:50', 'unique:jenis_ormawas,kode'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        JenisOrmawa::create([
            'nama_jenis' => $data['nama_jenis'],
            'kode' => $data['kode'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        return redirect()->route('admin.jenis-ormawas.index')->with('success', 'Jenis ormawa berhasil ditambahkan.');
    }

    public function edit(JenisOrmawa $jenisOrmava)
    {
        $this->authorizeMasterAccess();

        return view('admin.ormawa-masters.jenis.edit', ['item' => $jenisOrmava]);
    }

    public function update(Request $request, JenisOrmawa $jenisOrmava)
    {
        $this->authorizeMasterAccess();

        $data = $request->validate([
            'nama_jenis' => ['required', 'string', 'max:255'],
            'kode' => ['nullable', 'string', 'max:50', 'unique:jenis_ormawas,kode,' . $jenisOrmava->id],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $jenisOrmava->update([
            'nama_jenis' => $data['nama_jenis'],
            'kode' => $data['kode'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        return redirect()->route('admin.jenis-ormawas.index')->with('success', 'Jenis ormawa berhasil diperbarui.');
    }

    public function destroy(JenisOrmawa $jenisOrmava)
    {
        $this->authorizeMasterAccess();

        $jenisOrmava->delete();

        return redirect()->route('admin.jenis-ormawas.index')->with('success', 'Jenis ormawa berhasil dihapus.');
    }

    private function authorizeMasterAccess(): void
    {
        $user = auth()->user();
        abort_unless($user && ($user->isAdmin() || $user->hasRole('Kemahasiswaan') || $user->hasRole('Staf Kemahasiswaan')), 403);
    }
}
