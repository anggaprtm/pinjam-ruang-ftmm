<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\DosenDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DosenController extends Controller
{
    public function index()
    {
        // Ambil data user yang memiliki role 'Dosen' beserta detailnya
        $dosens = User::whereHas('roles', function($q) {
            $q->where('title', 'Dosen');
        })->with('dosenDetail')->get();

        return view('admin.dosen.index', compact('dosens'));
    }

    public function create()
    {
        return view('admin.dosen.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'nip' => 'required|unique:users,nip',
            'password' => 'required|min:6',
            'nidn' => 'nullable|unique:dosen_details,nidn',
            // Validasi detail lainnya opsional / nullable sesuai kebutuhan
        ]);

        DB::beginTransaction();
        try {
            // 1. Simpan ke tabel users
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'nip' => $request->nip,
                'password' => Hash::make($request->password),
            ]);

            // Assign role Dosen (Asumsi kamu pakai relasi standard atau Spatie)
            // Sesuaikan dengan logic assign role di projectmu, contoh:
            $user->roles()->attach(\App\Models\Role::where('title', 'Dosen')->first()->id);

            // 2. Simpan ke tabel dosen_details
            DosenDetail::create(array_merge($request->except(['name', 'email', 'nip', 'password']), ['user_id' => $user->id]));

            DB::commit();
            return redirect()->route('admin.dosen.index')->with('message', 'Data Dosen berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors('Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    public function edit(User $dosen)
    {
        $dosen->load('dosenDetail');
        return view('admin.dosen.form', compact('dosen'));
    }

    public function update(Request $request, User $dosen)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$dosen->id,
            'nip' => 'required|unique:users,nip,'.$dosen->id,
            'nidn' => 'nullable|unique:dosen_details,nidn,'.($dosen->dosenDetail->id ?? 'NULL'),
        ]);

        DB::beginTransaction();
        try {
            // 1. Update tabel users
            $userData = [
                'name' => $request->name,
                'email' => $request->email,
                'nip' => $request->nip,
            ];
            
            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }
            $dosen->update($userData);

            // 2. Update atau Create tabel dosen_details
            DosenDetail::updateOrCreate(
                ['user_id' => $dosen->id],
                $request->except(['_token', '_method', 'name', 'email', 'nip', 'password'])
            );

            DB::commit();
            return redirect()->route('admin.dosen.index')->with('message', 'Data Dosen berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors('Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    public function show(User $dosen)
    {
        // Load relasi detail dosen
        $dosen->load('dosenDetail');
        return view('admin.dosen.show', compact('dosen'));
    }

    public function destroy(User $dosen)
    {
        // Fitur hapus (Otomatis menghapus dosenDetail karena onDelete cascade di database)
        $dosen->delete();
        return redirect()->route('admin.dosen.index')->with('message', 'Data Dosen berhasil dihapus!');
    }
}