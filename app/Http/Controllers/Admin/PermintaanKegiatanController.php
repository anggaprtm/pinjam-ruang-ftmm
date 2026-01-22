<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PermintaanKegiatan;
use App\Models\User;
use App\Http\Requests\StorePermintaanRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class PermintaanKegiatanController extends Controller
{
    public function index()
    {
        // Tampilkan semua jika Admin/Pegawai, atau hanya milik sendiri jika User biasa
        $query = PermintaanKegiatan::with(['user', 'picUser']);
        
        if (!auth()->user()->isAdmin()) { // Sesuaikan logic role kamu
             $query->where('user_id', auth()->id())
                   ->orWhere('pic_user_id', auth()->id());
        }
        
        $permintaans = $query->latest()->get();

        return view('admin.permintaan.index', compact('permintaans'));
    }

    public function create()
    {
        // Dropdown PIC: Hanya user dengan role 'Pegawai' (Sesuaikan nama role di DB mu)
        $pics = User::whereHas('roles', function($q) {
            $q->where('title', 'Pegawai'); // Pastikan 'title' atau 'name' sesuai tabel roles
        })->pluck('name', 'id')->prepend('-- Pilih PIC --', '');

        return view('admin.permintaan.create', compact('pics'));
    }

    public function store(StorePermintaanRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();
        
        // Handle File Upload
        if ($request->hasFile('lampiran')) {
            $data['lampiran'] = $request->file('lampiran')->store('lampiran_kegiatan', 'public');
        }

        // Checkbox handling (HTML checkbox return 'on' or null, convert to boolean)
        $data['request_ruang'] = $request->has('request_ruang');
        $data['request_konsumsi'] = $request->has('request_konsumsi');

        // Set Initial Status
        $data['status_ruang'] = $data['request_ruang'] ? 'pending' : 'tidak_perlu';
        $data['status_konsumsi'] = $data['request_konsumsi'] ? 'pending' : 'tidak_perlu';
        $data['status_permintaan'] = 'pending';

        PermintaanKegiatan::create($data);

        return redirect()->route('admin.permintaan-kegiatan.index')->with('success', 'Permintaan berhasil diajukan.');
    }

    public function edit($id)
    {
        $permintaan = PermintaanKegiatan::findOrFail($id);

        // GUARD: Cek apakah user berhak edit (Pemilik atau Admin)
        if (auth()->user()->id !== $permintaan->user_id && !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized');
        }

        // GUARD: Cek apakah status masih pending (Belum diproses sama sekali)
        if ($permintaan->status_permintaan !== 'pending') {
            return redirect()->route('admin.permintaan-kegiatan.show', $id)
                ->with('error', 'Permintaan sedang diproses atau sudah selesai, tidak dapat diedit.');
        }

        // Ambil data PIC
        $pics = User::whereHas('roles', function($q) {
            $q->where('title', 'Pegawai'); 
        })->pluck('name', 'id')->prepend('-- Pilih PIC --', '');

        return view('admin.permintaan.edit', compact('permintaan', 'pics'));
    }

    public function update(StorePermintaanRequest $request, $id)
    {
        $permintaan = PermintaanKegiatan::findOrFail($id);

        // GUARD: Cek status sebelum update
        if ($permintaan->status_permintaan !== 'pending') {
            return back()->with('error', 'Permintaan sudah diproses, tidak bisa diubah.');
        }

        $data = $request->validated();
        
        // Update Checkbox
        $data['request_ruang'] = $request->has('request_ruang');
        $data['request_konsumsi'] = $request->has('request_konsumsi');

        // Update Status Sub-Item (Reset ke pending jika dicentang ulang)
        // Tapi jika sebelumnya 'selesai', jangan direset (tapi karena ini hanya boleh edit pas pending, aman direset)
        $data['status_ruang'] = $data['request_ruang'] ? 'pending' : 'tidak_perlu';
        $data['status_konsumsi'] = $data['request_konsumsi'] ? 'pending' : 'tidak_perlu';

        // Handle File
        if ($request->hasFile('lampiran')) {
            // Hapus file lama (Opsional)
            $data['lampiran'] = $request->file('lampiran')->store('lampiran_kegiatan', 'public');
        }

        $permintaan->update($data);

        return redirect()->route('admin.permintaan-kegiatan.index')->with('success', 'Permintaan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $permintaan = PermintaanKegiatan::findOrFail($id);

        // GUARD: Hanya pemilik atau admin
        if (auth()->user()->id !== $permintaan->user_id && !auth()->user()->isAdmin()) {
            abort(403);
        }

        // GUARD: Cek status sebelum hapus/batal
        if ($permintaan->status_permintaan !== 'pending') {
            return back()->with('error', 'Permintaan sudah diproses, tidak bisa dibatalkan.');
        }

        $permintaan->delete(); // Soft delete (dianggap batal)

        return redirect()->route('admin.permintaan-kegiatan.index')->with('success', 'Permintaan berhasil dibatalkan/dihapus.');
    }

    public function show($id)
    {
        $permintaan = PermintaanKegiatan::with(['user', 'picUser', 'kegiatan.ruangan'])->findOrFail($id);
        return view('admin.permintaan.show', compact('permintaan'));
    }

    // --- AKSI ADMIN KONSUMSI ---
    public function prosesKonsumsi(Request $request, $id)
    {
        $permintaan = PermintaanKegiatan::findOrFail($id);
        
        // Update status konsumsi jadi 'selesai' atau 'diproses'
        $permintaan->update([
            'status_konsumsi' => 'selesai',
            // Bisa tambah 'processed_by' jika mau track siapa adminnya
        ]);

        // Cek apakah request ini sudah bisa dianggap COMPLETED?
        $this->cekStatusSelesai($permintaan);

        return back()->with('success', 'Status konsumsi diperbarui.');
    }

    // Helper untuk cek final status
    private function cekStatusSelesai($permintaan)
    {
        $ruangOk = in_array($permintaan->status_ruang, ['selesai', 'tidak_perlu']);
        $konsumsiOk = in_array($permintaan->status_konsumsi, ['selesai', 'tidak_perlu']);

        if ($ruangOk && $konsumsiOk) {
            $permintaan->update(['status_permintaan' => 'selesai']);
        }
    }
}