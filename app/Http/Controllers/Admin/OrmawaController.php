<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisOrmawa;
use App\Models\Ormawa;
use App\Models\User;
use Illuminate\Http\Request;

class OrmawaController extends Controller
{
    public function index()
    {
        $this->authorizeMasterAccess();

        $items = Ormawa::with(['jenisOrmawa', 'users'])->orderBy('nama')->paginate(20);

        return view('admin.ormawa-masters.ormawa.index', compact('items'));
    }

    public function create()
    {
        $this->authorizeMasterAccess();

        $jenisOrmawas = JenisOrmawa::where('is_active', true)->orderBy('nama_jenis')->get();
        $users = User::orderBy('name')->get();

        return view('admin.ormawa-masters.ormawa.create', compact('jenisOrmawas', 'users'));
    }

    public function store(Request $request)
    {
        $this->authorizeMasterAccess();

        $data = $request->validate([
            'jenis_ormawa_id' => ['required', 'integer', 'exists:jenis_ormawas,id'],
            'nama' => ['required', 'string', 'max:255'],
            'kode' => ['nullable', 'string', 'max:50', 'unique:ormawas,kode'],
            'is_active' => ['nullable', 'boolean'],
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $ormawa = Ormawa::create([
            'jenis_ormawa_id' => $data['jenis_ormawa_id'],
            'nama' => $data['nama'],
            'kode' => $data['kode'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        $ormawa->users()->sync($data['user_ids'] ?? []);

        return redirect()->route('admin.ormawas-master.index')->with('success', 'Ormawa berhasil ditambahkan.');
    }

    public function edit(Ormawa $ormawasMaster)
    {
        $this->authorizeMasterAccess();

        $ormawasMaster->load('users');
        $jenisOrmawas = JenisOrmawa::where('is_active', true)->orderBy('nama_jenis')->get();
        $users = User::orderBy('name')->get();

        return view('admin.ormawa-masters.ormawa.edit', ['item' => $ormawasMaster, 'jenisOrmawas' => $jenisOrmawas, 'users' => $users]);
    }

    public function update(Request $request, Ormawa $ormawasMaster)
    {
        $this->authorizeMasterAccess();

        $data = $request->validate([
            'jenis_ormawa_id' => ['required', 'integer', 'exists:jenis_ormawas,id'],
            'nama' => ['required', 'string', 'max:255'],
            'kode' => ['nullable', 'string', 'max:50', 'unique:ormawas,kode,' . $ormawasMaster->id],
            'is_active' => ['nullable', 'boolean'],
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $ormawasMaster->update([
            'jenis_ormawa_id' => $data['jenis_ormawa_id'],
            'nama' => $data['nama'],
            'kode' => $data['kode'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        $ormawasMaster->users()->sync($data['user_ids'] ?? []);

        return redirect()->route('admin.ormawas-master.index')->with('success', 'Ormawa berhasil diperbarui.');
    }

    public function destroy(Ormawa $ormawasMaster)
    {
        $this->authorizeMasterAccess();

        $ormawasMaster->delete();

        return redirect()->route('admin.ormawas-master.index')->with('success', 'Ormawa berhasil dihapus.');
    }

    private function authorizeMasterAccess(): void
    {
        $user = auth()->user();
        abort_unless($user && ($user->isAdmin() || $user->hasRole('Kemahasiswaan') || $user->hasRole('Staf Kemahasiswaan')), 403);
    }
}
