<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PermintaanKegiatan;
use App\Models\User;
use App\Http\Requests\StorePermintaanRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class PermintaanKegiatanController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // PERBAIKAN DISINI: Pakai ->getTable()
            $query = PermintaanKegiatan::with(['user', 'picUser', 'kegiatan.ruangan'])
                ->select(sprintf('%s.*', (new PermintaanKegiatan)->getTable()));

            // --- FILTER USER ---
            if (!auth()->user()->isAdmin()) { 
                $query->where(function($q) {
                    $q->where('user_id', auth()->id())
                    ->orWhere('pic_user_id', auth()->id());
                });
            }

            // --- FILTER TANGGAL (Opsional jika ada input filter di view) ---
            if ($request->filled('tanggal_mulai')) {
                $query->whereDate('tanggal_kegiatan', '=', $request->tanggal_mulai);
            }

            $table = DataTables::of($query);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            // 1. KOLOM KEGIATAN (Title + Subtitle User)
            $table->editColumn('nama_kegiatan', function ($row) {
                $userName = $row->user->name ?? '-';
                $createdHuman = $row->created_at->diffForHumans();
                
                return '<div class="kegiatan-title-cell">'.$row->nama_kegiatan.'</div>
                        <div class="d-flex align-items-center mt-1">
                            <div class="user-avatar bg-secondary text-white d-flex justify-content-center align-items-center rounded-circle me-2" style="width:20px;height:20px;font-size:10px;">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <div class="kegiatan-sub-cell text-muted small">Pemohon: '.$userName.'</div>
                                <div class="text-muted" style="font-size: 10px;">Dibuat: '.$createdHuman.'</div>
                            </div>
                        </div>';
            });

            // 2. KOLOM WAKTU (Tanggal + Jam)
            $table->editColumn('tanggal_kegiatan', function ($row) {
                $tgl = Carbon::parse($row->tanggal_kegiatan)->translatedFormat('d M Y');
                $jam = Carbon::parse($row->waktu_mulai)->format('H:i') . ' - ' . Carbon::parse($row->waktu_selesai)->format('H:i');
                return '<div class="fw-bold text-dark">'.$tgl.'</div><div class="small text-muted"><i class="far fa-clock me-1"></i>'.$jam.'</div>';
            });

            // 3. STATUS RUANG
            $table->editColumn('status_ruang', function ($row) {
                if ($row->status_ruang == 'selesai') {
                    // Tampilkan nama ruangan dengan style abu-abu
                    $ruangNama = $row->kegiatan->ruangan->nama ?? '-';
                    return '<span class="badge-pill-modern badge-soft-secondary">'.$ruangNama.'</span>';
                } elseif ($row->status_ruang == 'pending') {
                    return '<span class="badge-pill-modern badge-soft-warning">PENDING</span>';
                }
                return '<span class="text-muted small">-</span>';
            });

            // 2. STATUS KONSUMSI
            $table->editColumn('status_konsumsi', function ($row) {
                if ($row->status_konsumsi == 'tidak_perlu') {
                    return '<span class="text-muted small">-</span>';
                }
                
                $status = $row->status_konsumsi;
                $label = strtoupper($status); // PENDING, DIPROSES, SELESAI
                
                $cls = 'badge-soft-secondary';
                if ($status == 'pending') $cls = 'badge-soft-warning';
                if ($status == 'diproses') $cls = 'badge-soft-info';
                if ($status == 'selesai') $cls = 'badge-soft-success';

                return '<span class="badge-pill-modern '.$cls.'">'.$label.'</span>';
            });

            /// 3. STATUS PERMINTAAN (UTAMA)
            $table->editColumn('status_permintaan', function ($row) {
                $status = $row->status_permintaan;
                $label = strtoupper($status);

                $cls = 'badge-soft-secondary';
                if ($status == 'pending') $cls = 'badge-soft-warning';
                if ($status == 'proses') $cls = 'badge-soft-info';
                if ($status == 'selesai') $cls = 'badge-soft-success';
                if ($status == 'ditolak') $cls = 'badge-soft-danger';

                return '<span class="badge-pill-modern '.$cls.'">'.$label.'</span>';
            });

            // 6. ACTIONS
            $table->editColumn('actions', function ($row) {
                $btn = '<a class="btn btn-xs btn-info" href="' . route('admin.permintaan-kegiatan.show', $row->id) . '" title="Detail"><i class="fas fa-eye"></i></a> ';
                
                // Edit/Delete hanya jika pending
                if ($row->status_permintaan == 'pending' && (auth()->user()->id == $row->user_id || auth()->user()->isAdmin())) {
                    $btn .= '<a class="btn btn-xs btn-success" href="' . route('admin.permintaan-kegiatan.edit', $row->id) . '" title="Edit"><i class="fas fa-edit"></i></a> ';
                    
                    $btn .= '<form action="'.route('admin.permintaan-kegiatan.destroy', $row->id).'" method="POST" onsubmit="return confirm(\'Batalkan permintaan ini?\');" style="display: inline-block;">
                                <input type="hidden" name="_method" value="DELETE">
                                <input type="hidden" name="_token" value="'.csrf_token().'">
                                <button type="submit" class="btn btn-xs btn-danger" title="Batalkan"><i class="fas fa-trash-alt"></i></button>
                            </form>';
                }
                return $btn;
            });

            $table->rawColumns(['actions', 'placeholder', 'nama_kegiatan', 'tanggal_kegiatan', 'status_ruang', 'status_konsumsi', 'status_permintaan']);

            return $table->make(true);
        }

        return view('admin.permintaan.index');
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