<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\TendikDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class TendikController extends Controller
{
    public function index()
    {
        // Ingat: Role yang digunakan di sistemmu adalah 'Pegawai'
        $tendiks = User::whereHas('roles', function($q) {
            $q->where('title', 'Pegawai');
        })->with('tendikDetail')->get();

        return view('admin.tendik.index', compact('tendiks'));
    }

    public function create()
    {
        return view('admin.tendik.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'nip' => 'required|unique:users,nip',
            'password' => 'required|min:6',
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'nip' => $request->nip,
                'password' => Hash::make($request->password),
            ]);

            // Assign role 'Pegawai'
            $user->roles()->attach(\App\Models\Role::where('title', 'Pegawai')->first()->id);

            TendikDetail::create(array_merge($request->except(['name', 'email', 'nip', 'password']), ['user_id' => $user->id]));

            DB::commit();
            return redirect()->route('admin.tendik.index')->with('message', 'Data Tendik berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors('Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function show(User $tendik)
    {
        $tendik->load('tendikDetail');
        return view('admin.tendik.show', compact('tendik'));
    }

    public function edit(User $tendik)
    {
        $tendik->load('tendikDetail');
        return view('admin.tendik.form', compact('tendik'));
    }

    public function update(Request $request, User $tendik)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$tendik->id,
            'nip' => 'required|unique:users,nip,'.$tendik->id,
        ]);

        DB::beginTransaction();
        try {
            $userData = [
                'name' => $request->name,
                'email' => $request->email,
                'nip' => $request->nip,
            ];
            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }
            $tendik->update($userData);

            TendikDetail::updateOrCreate(
                ['user_id' => $tendik->id],
                $request->except(['_token', '_method', 'name', 'email', 'nip', 'password'])
            );

            DB::commit();
            return redirect()->route('admin.tendik.index')->with('message', 'Data Tendik berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors('Gagal memperbarui: ' . $e->getMessage());
        }
    }

    public function destroy(User $tendik)
    {
        $tendik->delete();
        return redirect()->route('admin.tendik.index')->with('message', 'Data Tendik berhasil dihapus!');
    }
}