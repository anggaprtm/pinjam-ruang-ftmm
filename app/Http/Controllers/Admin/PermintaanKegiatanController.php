<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PermintaanKegiatan;
use App\Models\User;
use App\Http\Requests\StorePermintaanRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http; // <--- WAJIB IMPORT INI
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class PermintaanKegiatanController extends Controller
{
    // --- KONFIGURASI ID OPERATOR (Sesuaikan dengan ID User di Database kamu) ---
    // Bisa satu atau banyak ID dalam array
    private $operatorRuangIds    = [1, 24]; // Contoh: ID User Admin Sarpras
    private $operatorKonsumsiIds = [20];    // Contoh: ID User Admin Umum/Konsumsi

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = PermintaanKegiatan::with(['user', 'picUser', 'kegiatan.ruangan'])
                ->select(sprintf('%s.*', (new PermintaanKegiatan)->getTable()));

            if (!auth()->user()->isAdmin()) { 
                $query->where(function($q) {
                    $q->where('user_id', auth()->id())
                    ->orWhere('pic_user_id', auth()->id());
                });
            }

            if ($request->filled('tanggal_mulai')) {
                $query->whereDate('tanggal_kegiatan', '=', $request->tanggal_mulai);
            }

            $table = DataTables::of($query);
            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

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

            $table->editColumn('tanggal_kegiatan', function ($row) {
                $tgl = Carbon::parse($row->tanggal_kegiatan)->translatedFormat('d M Y');
                $jam = Carbon::parse($row->waktu_mulai)->format('H:i') . ' - ' . Carbon::parse($row->waktu_selesai)->format('H:i');
                return '<div class="fw-bold text-dark">'.$tgl.'</div><div class="small text-muted"><i class="far fa-clock me-1"></i>'.$jam.'</div>';
            });

            $table->editColumn('status_ruang', function ($row) {
                if ($row->status_ruang == 'selesai') {
                    $ruangNama = $row->kegiatan->ruangan->nama ?? '-';
                    return '<span class="badge-pill-modern badge-soft-secondary">'.$ruangNama.'</span>';
                } elseif ($row->status_ruang == 'pending') {
                    return '<span class="badge-pill-modern badge-soft-warning">PENDING</span>';
                }
                return '<span class="text-muted small">-</span>';
            });

            $table->editColumn('status_konsumsi', function ($row) {
                if ($row->status_konsumsi == 'tidak_perlu') {
                    return '<span class="text-muted small">-</span>';
                }
                
                $status = $row->status_konsumsi;
                $label = strtoupper($status);
                
                $cls = 'badge-soft-secondary';
                if ($status == 'pending') $cls = 'badge-soft-warning';
                if ($status == 'diproses') $cls = 'badge-soft-info';
                if ($status == 'selesai') $cls = 'badge-soft-success';

                return '<span class="badge-pill-modern '.$cls.'">'.$label.'</span>';
            });

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

            $table->editColumn('actions', function ($row) {
                $btn = '<a class="btn btn-xs btn-info" href="' . route('admin.permintaan-kegiatan.show', $row->id) . '" title="Detail"><i class="fas fa-eye"></i></a> ';
                
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
        $pics = User::whereHas('roles', function($q) {
            $q->where('title', 'Pegawai'); 
        })->pluck('name', 'id')->prepend('-- Pilih PIC --', '');

        return view('admin.permintaan.create', compact('pics'));
    }

    public function store(StorePermintaanRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();
        
        if ($request->hasFile('lampiran')) {
            $data['lampiran'] = $request->file('lampiran')->store('lampiran_kegiatan', 'public');
        }

        $data['request_ruang'] = $request->has('request_ruang');
        $data['request_konsumsi'] = $request->has('request_konsumsi');

        $data['status_ruang'] = $data['request_ruang'] ? 'pending' : 'tidak_perlu';
        $data['status_konsumsi'] = $data['request_konsumsi'] ? 'pending' : 'tidak_perlu';
        $data['status_permintaan'] = 'pending';

        $permintaan = PermintaanKegiatan::create($data);

        // --- NOTIFIKASI TELEGRAM KE OPERATOR ---
        try {
            $this->notifyNewRequest($permintaan);
        } catch (\Exception $e) {
            // Silent fail: Jangan sampai error telegram bikin error aplikasi
            // \Log::error("Telegram Error: " . $e->getMessage());
        }

        return redirect()->route('admin.permintaan-kegiatan.index')->with('success', 'Permintaan berhasil diajukan.');
    }

    public function edit($id)
    {
        $permintaan = PermintaanKegiatan::findOrFail($id);

        if (auth()->user()->id !== $permintaan->user_id && !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized');
        }

        if ($permintaan->status_permintaan !== 'pending') {
            return redirect()->route('admin.permintaan-kegiatan.show', $id)
                ->with('error', 'Permintaan sedang diproses atau sudah selesai, tidak dapat diedit.');
        }

        $pics = User::whereHas('roles', function($q) {
            $q->where('title', 'Pegawai'); 
        })->pluck('name', 'id')->prepend('-- Pilih PIC --', '');

        return view('admin.permintaan.edit', compact('permintaan', 'pics'));
    }

    public function update(StorePermintaanRequest $request, $id)
    {
        $permintaan = PermintaanKegiatan::findOrFail($id);

        if ($permintaan->status_permintaan !== 'pending') {
            return back()->with('error', 'Permintaan sudah diproses, tidak bisa diubah.');
        }

        $data = $request->validated();
        $data['request_ruang'] = $request->has('request_ruang');
        $data['request_konsumsi'] = $request->has('request_konsumsi');
        $data['status_ruang'] = $data['request_ruang'] ? 'pending' : 'tidak_perlu';
        $data['status_konsumsi'] = $data['request_konsumsi'] ? 'pending' : 'tidak_perlu';

        if ($request->hasFile('lampiran')) {
            $data['lampiran'] = $request->file('lampiran')->store('lampiran_kegiatan', 'public');
        }

        $permintaan->update($data);

        return redirect()->route('admin.permintaan-kegiatan.index')->with('success', 'Permintaan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $permintaan = PermintaanKegiatan::findOrFail($id);

        if (auth()->user()->id !== $permintaan->user_id && !auth()->user()->isAdmin()) {
            abort(403);
        }

        if ($permintaan->status_permintaan !== 'pending') {
            return back()->with('error', 'Permintaan sudah diproses, tidak bisa dibatalkan.');
        }

        $permintaan->delete(); 
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
        
        $permintaan->update([
            'status_konsumsi' => 'selesai',
        ]);

        // NOTIFIKASI KE PEMOHON
        try {
            $this->notifyUpdateToUser($permintaan, 'KONSUMSI');
        } catch (\Exception $e) {}

        $this->cekStatusSelesai($permintaan);

        return back()->with('success', 'Status konsumsi diperbarui.');
    }

    private function cekStatusSelesai($permintaan)
    {
        $ruangOk = in_array($permintaan->status_ruang, ['selesai', 'tidak_perlu']);
        $konsumsiOk = in_array($permintaan->status_konsumsi, ['selesai', 'tidak_perlu']);

        if ($ruangOk && $konsumsiOk) {
            $permintaan->update(['status_permintaan' => 'selesai']);
            
            // NOTIFIKASI FINAL (PERMINTAAN SELESAI)
            try {
                $this->notifyUpdateToUser($permintaan, 'FINAL');
            } catch (\Exception $e) {}
        }
    }


    // ==========================================
    // TELEGRAM NOTIFICATION HELPER
    // ==========================================

    /**
     * Kirim Notifikasi ke Operator (Ruang & Konsumsi) saat ada Request Baru
     */
    private function notifyNewRequest($permintaan)
    {
        $pemohon = $permintaan->user->name ?? 'User';
        $tanggal = Carbon::parse($permintaan->tanggal_kegiatan)->translatedFormat('d F Y');
        $jam     = Carbon::parse($permintaan->waktu_mulai)->format('H:i');

        $message = "🔔 <b>PERMINTAAN LAYANAN BARU</b>\n\n" .
                   "👤 <b>Pemohon:</b> $pemohon\n" .
                   "📅 <b>Tanggal:</b> $tanggal\n" .
                   "⏰ <b>Jam:</b> $jam\n" .
                   "📝 <b>Kegiatan:</b> {$permintaan->nama_kegiatan}\n\n" .
                   "Mohon segera dicek di dashboard admin.";

        // 1. Kirim ke Operator Ruang (Jika butuh ruang)
        if ($permintaan->request_ruang) {
            $targetUsers = User::whereIn('id', $this->operatorRuangIds)->get();
            foreach ($targetUsers as $user) {
                if ($user->telegram_chat_id) {
                    $this->sendTelegram($user->telegram_chat_id, $message . "\n\n(Kategori: 🏠 Ruangan)");
                }
            }
        }

        // 2. Kirim ke Operator Konsumsi (Jika butuh konsumsi)
        if ($permintaan->request_konsumsi) {
            $targetUsers = User::whereIn('id', $this->operatorKonsumsiIds)->get();
            foreach ($targetUsers as $user) {
                if ($user->telegram_chat_id) {
                    $this->sendTelegram($user->telegram_chat_id, $message . "\n\n(Kategori: 🍱 Konsumsi)");
                }
            }
        }
    }

    /**
     * Kirim Notifikasi ke Pemohon saat status berubah
     */
    private function notifyUpdateToUser($permintaan, $type)
    {
        $chatId = $permintaan->user->telegram_chat_id;
        
        if (!$chatId) return; // User gak punya telegram, skip

        $kegiatan = $permintaan->nama_kegiatan;

        if ($type == 'KONSUMSI') {
            $msg = "🍱 <b>UPDATE KONSUMSI</b>\n\n" .
                   "Halo, Permintaan konsumsi untuk kegiatan <b>$kegiatan</b> telah <b>DISETUJUI/DIPROSES</b> oleh admin.\n\n" .
                   "Silakan koordinasi lebih lanjut jika diperlukan.";
        } elseif ($type == 'FINAL') {
            $msg = "✅ <b>PERMINTAAN SELESAI</b>\n\n" .
                   "Halo, Seluruh permintaan layanan (Ruang/Konsumsi) untuk kegiatan <b>$kegiatan</b> telah <b>SELESAI</b> diproses.\n\n" .
                   "Terima kasih.";
        } else {
            return;
        }

        $this->sendTelegram($chatId, $msg);
    }

    /**
     * Fungsi Dasar Kirim Pesan Telegram
     */
    private function sendTelegram($chatId, $message)
    {
        // Pastikan TELEGRAM_BOT_TOKEN ada di .env
        $token = env('TELEGRAM_BOT_TOKEN'); 
        
        if (!$token || !$chatId) return;

        Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
            'chat_id' => $chatId,
            'text'    => $message,
            'parse_mode' => 'HTML'
        ]);
    }
}