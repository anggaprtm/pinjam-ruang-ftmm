<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JadwalWfh;
use App\Models\User;
use Illuminate\Http\Request;

class JadwalWfhController extends Controller
{
    /**
     * Menampilkan daftar jadwal WFH.
     */
    public function index()
    {
        // FIX: Ubah 'user' menjadi 'users' (jamak)
        $jadwals = JadwalWfh::with('users')->orderBy('created_at', 'desc')->get();
        return view('admin.jadwal-wfh.index', compact('jadwals'));
    }

    /**
     * Menampilkan form tambah jadwal.
     */
    public function create()
    {
        // Ambil daftar pegawai & dosen yang aktif untuk pilihan spesifik
        $pegawais = User::whereHas('roles', function($q) {
            $q->whereIn('title', ['Pegawai', 'Dosen']);
        })->orderBy('name', 'asc')->get();

        return view('admin.jadwal-wfh.form', compact('pegawais'));
    }

   public function store(Request $request)
    {
        $this->validateJadwal($request);

        // FIX: Kecualikan input bantuan UI agar tidak ikut di-insert ke DB
        $data = $request->except(['user_ids', 'tipe_waktu', 'sasaran']); 
        
        $data['is_global'] = $request->sasaran === 'semua';
        $this->formatData($data, $request);

        $jadwal = JadwalWfh::create($data);

        // Simpan banyak pegawai ke tabel pivot
        if (!$data['is_global'] && $request->has('user_ids')) {
            $jadwal->users()->sync($request->user_ids);
        }

        return redirect()->route('admin.jadwal-wfh.index')
            ->with('success', 'Jadwal WFH berhasil ditambahkan!');
    }

    /**
     * Menampilkan form edit jadwal.
     */
    public function edit($id)
    {
        $jadwal = JadwalWfh::findOrFail($id);
        $pegawais = User::whereHas('roles', function($q) {
            $q->whereIn('title', ['Pegawai', 'Dosen']);
        })->orderBy('name', 'asc')->get();

        return view('admin.jadwal-wfh.form', compact('jadwal', 'pegawais'));
    }

    /**
     * Memperbarui data jadwal WFH.
     */
     public function update(Request $request, $id)
    {
        $jadwal = JadwalWfh::findOrFail($id);
        $this->validateJadwal($request);

        // FIX: Kecualikan input bantuan UI
        $data = $request->except(['user_ids', 'tipe_waktu', 'sasaran']);
        
        $data['is_global'] = $request->sasaran === 'semua';
        $this->formatData($data, $request);

        $jadwal->update($data);

        // Update pegawai di tabel pivot
        if (!$data['is_global'] && $request->has('user_ids')) {
            $jadwal->users()->sync($request->user_ids);
        } else {
            $jadwal->users()->detach();
        }

        return redirect()->route('admin.jadwal-wfh.index')
            ->with('success', 'Jadwal WFH berhasil diperbarui!');
    }

    /**
     * Menghapus jadwal WFH.
     */
    public function destroy($id)
    {
        $jadwal = JadwalWfh::findOrFail($id);
        $jadwal->delete();

        return back()->with('success', 'Jadwal WFH berhasil dihapus!');
    }

    /**
     * Logika Validasi Form.
     */
    

    private function validateJadwal(Request $request)
    {
        return $request->validate([
            'keterangan'  => 'required|string|max:255',
            'tipe_waktu'  => 'required|in:rutin,insidental',
            'sasaran'     => 'required|in:semua,spesifik',
            'hari_rutin'  => 'required_if:tipe_waktu,rutin|nullable|integer|between:1,7',
            'tanggal'     => 'required_if:tipe_waktu,insidental|nullable|date',
            'user_ids'    => 'required_if:sasaran,spesifik|array', // Ubah ke array
            'user_ids.*'  => 'exists:users,id'
        ]);
    }

    /**
     * Helper untuk membersihkan data yang tidak relevan dengan pilihan user.
     */
    private function formatData(&$data, $request)
    {
        // Jika pilih rutin, maka tanggal harus null
        if ($request->tipe_waktu === 'rutin') {
            $data['tanggal'] = null;
        } else {
            $data['hari_rutin'] = null;
        }
    }
}