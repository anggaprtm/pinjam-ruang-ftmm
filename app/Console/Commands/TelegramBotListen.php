<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Models\ProductivityNote;
use App\Models\ProductivityTask;
use App\Models\AgendaFakultas;
use App\Models\Kegiatan;
use App\Models\Ruangan;
use App\Services\EventService;
use Carbon\Carbon;

class TelegramBotListen extends Command
{
    protected $signature = 'telegram:listen';
    protected $description = 'Menjalankan bot Telegram listener (Long Polling)';

    protected $token;
    protected $baseUrl;

    public function __construct()
    {
        parent::__construct();
        $this->token = env('TELEGRAM_BOT_TOKEN');
        $this->baseUrl = "https://api.telegram.org/bot{$this->token}";
    }

    public function handle()
    {
        $this->info("🤖 Bot sedang berjalan... Menunggu perintah /start, /note, /task, atau /remind");
        $this->info("Tekan Ctrl+C untuk berhenti.");

        $offset = 0;

        while (true) {
            try {
                $response = Http::timeout(40)->get("{$this->baseUrl}/getUpdates", [
                    'offset' => $offset,
                    'timeout' => 30 
                ]);

                if ($response->successful()) {
                    $result = $response->json('result');

                    foreach ($result as $update) {
                        $offset = $update['update_id'] + 1;

                        // 1. HANDLE PESAN TEKS BIASA
                        if (isset($update['message']['text'])) {
                            $chatId = $update['message']['chat']['id'];
                            $text = $update['message']['text'];
                            $firstName = $update['message']['from']['first_name'] ?? 'User';

                            $textLower = strtolower($text);

                            if ($textLower === '/start') {
                                $this->replyWithId($chatId, $firstName);
                            } elseif ($textLower === '/help' || $textLower === '/menu') {
                                $this->sendHelpMenu($chatId);
                            } elseif (str_starts_with($textLower, '/note ')) {
                                $this->handleNoteCommand($chatId, $text);
                            } elseif (str_starts_with($textLower, '/task ')) {
                                $this->handleTaskOrRemind($chatId, $text, 'task');
                            } elseif (str_starts_with($textLower, '/remind ')) {
                                $this->handleTaskOrRemind($chatId, $text, 'remind');
                            } elseif ($textLower === '/list' || $textLower === '/hariini') {
                                $this->handleListCommand($chatId); 
                            } 
                            // --- FITUR BARU MULAI DARI SINI ---
                            elseif ($textLower === '/agenda') {
                                $this->handleListAgenda($chatId);
                            } elseif (str_starts_with($textLower, '/addagenda ')) {
                                $this->handleAddAgenda($chatId, $text);
                            } elseif ($textLower === '/kegiatan') {
                                $this->handleListKegiatan($chatId);
                            } elseif (str_starts_with($textLower, '/book ')) {
                                $eventService = app(EventService::class);
                                $this->handleBookCommand($chatId, $text, $eventService);
                            }
                        } 
                        // 2. HANDLE KLIK TOMBOL (CALLBACK QUERY)
                        elseif (isset($update['callback_query'])) {
                            $this->handleCallbackQuery($update['callback_query']);
                        }
                    }
                }
            } catch (\Exception $e) {
                $this->error("Error koneksi: " . $e->getMessage());
                sleep(5); 
            }
        }
    }

    private function replyWithId($chatId, $name)
    {
        $message = "Halo, <b>$name</b>! 👋\n\nID Telegram kamu: <code>$chatId</code>\n\nSilakan copy angka di atas dan paste ke halaman Profil di website FTMM-Nexus.";
        Http::post("{$this->baseUrl}/sendMessage", ['chat_id' => $chatId, 'text' => $message, 'parse_mode' => 'HTML']);
    }

    private function sendHelpMenu($chatId)
    {
        $msg = "🤖 <b>Menu Produktivitas Bot</b>\n\n";
        $msg .= "📝 <b>Catatan Cepat:</b>\n<code>/note [isi]</code>\n\n";
        $msg .= "✅ <b>Tugas Baru:</b>\n<code>/task [judul] @[waktu]</code>\n\n";
        $msg .= "⏰ <b>Pengingat:</b>\n<code>/remind [pesan] @[waktu]</code>\n\n";
        $msg .= "📋 <b>Jadwal Hari Ini:</b>\n<code>/list</code> atau <code>/hariini</code>\n\n";
        $msg .= "🏛️ <b>Cek Agenda Fakultas:</b> <code>/agenda</code>\n";
        $msg .= "🏢 <b>Jadwal Ruangan Hari Ini:</b> <code>/kegiatan</code>\n\n";
        $msg .= "🏢 <b>Booking Ruangan:</b>\n<code>/book [Acara] @[Ruang] @[Tanggal/Hari] [Jam Mulai]-[Jam Selesai]</code>\n";
        $msg .= "<i>Contoh: /book Rapat Internal @GC-701 @besok 09:00-11:00</i>\n\n";
        $msg .= "<i>Contoh Format Waktu yang didukung:</i>\n";
        $msg .= "- <code>@besok 15:00</code>\n- <code>@lusa</code>\n- <code>@jumat 09.30</code>\n- <code>@15 april 2026</code>\n- <code>@16:00</code> (hari ini)";

        Http::post("{$this->baseUrl}/sendMessage", ['chat_id' => $chatId, 'text' => $msg, 'parse_mode' => 'HTML']);
    }

    private function handleNoteCommand($chatId, $text)
    {
        $user = User::where('telegram_chat_id', (string)$chatId)->first();
        if (!$user) { $this->replyUnregistered($chatId); return; }

        $content = trim(substr($text, 6));
        if (empty($content)) return;

        $colors = ['#fef08a', '#bbf7d0', '#bfdbfe', '#fbcfe8', '#fed7aa', '#e9d5ff'];
        ProductivityNote::create(['user_id' => $user->id, 'content' => $content, 'bg_color' => $colors[array_rand($colors)]]);

        Http::post("{$this->baseUrl}/sendMessage", ['chat_id' => $chatId, 'text' => "✅ <b>Catatan disimpan!</b>", 'parse_mode' => 'HTML']);
    }


    private function handleListCommand($chatId)
    {
        $user = User::where('telegram_chat_id', (string)$chatId)->first();
        if (!$user) { $this->replyUnregistered($chatId); return; }

        // Cari tugas yang pending dan deadline-nya hari ini atau sebelumnya (terlambat)
        $tasks = ProductivityTask::where('user_id', $user->id)
                    ->where('status', 'pending')
                    ->where(function($q) {
                        $q->whereDate('deadline_at', '<=', Carbon::today())
                          ->orWhereNull('deadline_at'); // Termasuk yang tanpa tenggat
                    })
                    ->orderBy('deadline_at', 'asc')
                    ->get();

        if ($tasks->isEmpty()) {
            Http::post("{$this->baseUrl}/sendMessage", [
                'chat_id' => $chatId,
                'text' => "🎉 Tidak ada tugas mendesak untuk hari ini. Waktunya bersantai!"
            ]);
            return;
        }

        $msg = "📋 <b>Daftar Tugas Aktif & Mendesak:</b>\n\n";
        Http::post("{$this->baseUrl}/sendMessage", ['chat_id' => $chatId, 'text' => $msg, 'parse_mode' => 'HTML']);

        // Kirim tugas satu per satu dengan tombol Selesai di bawahnya
        foreach ($tasks as $task) {
            $dlText = $task->deadline_at ? Carbon::parse($task->deadline_at)->translatedFormat('d M, H:i') : 'Tanpa Tenggat';
            $taskMsg = "📌 <b>{$task->title}</b>\n⏳ Deadline: $dlText";

            // Bikin tombol Interaktif
            $keyboard = [
                'inline_keyboard' => [
                    [
                        ['text' => '✅ Tandai Selesai', 'callback_data' => "complete_task_{$task->id}"]
                    ]
                ]
            ];

            Http::post("{$this->baseUrl}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $taskMsg,
                'parse_mode' => 'HTML',
                'reply_markup' => json_encode($keyboard) // Pasang tombol di sini
            ]);
        }
    }


    /**
     * Menangani aksi ketika tombol di Telegram diklik
     */
    private function handleCallbackQuery($callbackQuery)
    {
        $callbackId = $callbackQuery['id'];
        $chatId = $callbackQuery['message']['chat']['id'];
        $messageId = $callbackQuery['message']['message_id'];
        $data = $callbackQuery['data']; // contoh isinya: complete_task_5

        // Jika tombol Selesai diklik
        if (str_starts_with($data, 'complete_task_')) {
            $taskId = str_replace('complete_task_', '', $data);
            
            $task = ProductivityTask::find($taskId);
            if ($task) {
                // Update ke database
                $task->update(['status' => 'completed']);

                // Hapus tombol dari pesan dan ubah teksnya jadi dicoret/selesai
                $newText = "✅ <s>{$task->title}</s>\n<i>Telah diselesaikan via Telegram!</i>";

                Http::post("{$this->baseUrl}/editMessageText", [
                    'chat_id' => $chatId,
                    'message_id' => $messageId,
                    'text' => $newText,
                    'parse_mode' => 'HTML'
                ]);

                // Beri notif pop-up kecil di layar HP user
                Http::post("{$this->baseUrl}/answerCallbackQuery", [
                    'callback_query_id' => $callbackId,
                    'text' => "Mantap! Tugas selesai 🎉"
                ]);
            }
        }
    }

    /**
     * Handle untuk /task dan /remind dengan Smart Date Parsing
     */
    private function handleTaskOrRemind($chatId, $text, $type)
    {
        $user = User::where('telegram_chat_id', (string)$chatId)->first();
        if (!$user) { $this->replyUnregistered($chatId); return; }

        $prefixLen = ($type === 'task') ? 6 : 8; // '/task ' atau '/remind '
        $rawInput = trim(substr($text, $prefixLen));
        if (empty($rawInput)) return;

        $title = $rawInput;
        $deadline = null;
        $replyMsgTime = "<b>tanpa tenggat waktu</b>.";

        // Jika menggunakan simbol '@' untuk set waktu
        if (str_contains($rawInput, '@')) {
            $parts = explode('@', $rawInput);
            $title = trim($parts[0]);
            $timeStr = strtolower(trim($parts[1]));

            $parsedDate = $this->parseSmartDate($timeStr);

            if ($parsedDate) {
                $deadline = $parsedDate->format('Y-m-d H:i:s');
                $replyMsgTime = "pada <b>" . $parsedDate->translatedFormat('l, d M Y - H:i') . " WIB</b>.";
            } else {
                $replyMsgTime = "<i>(Format waktu tidak dikenali, diset tanpa tenggat)</i>.";
            }
        }

        // Tentukan Tag dan Prioritas berdasarkan perintah
        $tag = ($type === 'remind') ? '⏰ Pengingat' : 'Dari Telegram';
        $priority = ($type === 'remind') ? 'high' : 'medium';
        $icon = ($type === 'remind') ? '⏰' : '✅';

        ProductivityTask::create([
            'user_id' => $user->id,
            'title' => $title,
            'priority' => $priority,
            'tag' => $tag, 
            'deadline_at' => $deadline,
        ]);

        $msg = "$icon <b>Berhasil ditambahkan!</b>\nDisimpan $replyMsgTime";
        Http::post("{$this->baseUrl}/sendMessage", ['chat_id' => $chatId, 'text' => $msg, 'parse_mode' => 'HTML']);
    }

    /**
     * NLP Sederhana untuk membaca format hari/tanggal bahasa Indonesia
     */
    private function parseSmartDate($timeStr)
    {
        // Kamus terjemahan ke format English yang dipahami Carbon
        $translate = [
            'besok' => 'tomorrow', 'lusa' => '+2 days',
            'senin' => 'next monday', 'selasa' => 'next tuesday', 'rabu' => 'next wednesday',
            'kamis' => 'next thursday', 'jumat' => 'next friday', 'sabtu' => 'next saturday', 'minggu' => 'next sunday',
            'januari' => 'january', 'februari' => 'february', 'maret' => 'march', 'agustus' => 'august',
            'oktober' => 'october', 'desember' => 'december', 'mei' => 'may', 'agu' => 'aug', 'ags' => 'aug'
        ];

        // Default: Kalau tidak ada jam, set ke 23:59
        $timePart = '23:59';
        $datePart = $timeStr;

        // Ekstrak Jam (Cari format HH:MM atau HH.MM di akhir teks)
        if (preg_match('/([01]?[0-9]|2[0-3])[:.]([0-5][0-9])$/', $timeStr, $timeMatch)) {
            $timePart = $timeMatch[1] . ':' . $timeMatch[2]; // Paksa format pakai titik dua
            $datePart = trim(str_replace($timeMatch[0], '', $timeStr)); // Sisa teks adalah tanggal
        }

        // Jika ternyata tanggal kosong (user cuma ngetik jam, misal "@15:00")
        if (empty($datePart)) {
            $datePart = 'today';
        }

        // Translasi Bahasa Indonesia -> English
        $datePartEng = str_ireplace(array_keys($translate), array_values($translate), $datePart);

        try {
            // Biarkan Carbon yang melakukan keajaiban parsing
            return Carbon::parse("$datePartEng $timePart");
        } catch (\Exception $e) {
            return null; // Jika gagal dibaca sama sekali
        }
    }

    private function replyUnregistered($chatId)
    {
        $msg = "❌ Akun Telegram Anda belum ditautkan.\n\nMasukkan ID <code>$chatId</code> di web.";
        Http::post("{$this->baseUrl}/sendMessage", ['chat_id' => $chatId, 'text' => $msg, 'parse_mode' => 'HTML']);
    }

    /**
     * FITUR: Menampilkan Agenda Fakultas Bulan Ini
     */
    private function handleListAgenda($chatId)
    {
        $now = Carbon::now();
        
        $agendas = AgendaFakultas::whereMonth('tanggal_mulai', $now->month)
                    ->whereYear('tanggal_mulai', $now->year)
                    ->orderBy('tanggal_mulai', 'asc')
                    ->get();

        if ($agendas->isEmpty()) {
            Http::post("{$this->baseUrl}/sendMessage", ['chat_id' => $chatId, 'text' => "📅 Tidak ada Agenda Fakultas di bulan " . $now->translatedFormat('F Y')]);
            return;
        }

        $msg = "🏛️ <b>Agenda Fakultas (" . $now->translatedFormat('F Y') . ")</b>\n\n";
        foreach ($agendas as $agenda) {
            $tgl = Carbon::parse($agenda->tanggal_mulai)->translatedFormat('d M');
            $msg .= "🔹 <b>{$tgl}</b> - {$agenda->judul}\n";
        }

        Http::post("{$this->baseUrl}/sendMessage", ['chat_id' => $chatId, 'text' => $msg, 'parse_mode' => 'HTML']);
    }

    /**
     * FITUR: Tambah Agenda Fakultas Baru via Telegram
     */
    private function handleAddAgenda($chatId, $text)
    {
        $user = User::where('telegram_chat_id', (string)$chatId)->first();
        if (!$user) { $this->replyUnregistered($chatId); return; }

        // Pastikan hanya atasan/admin yang bisa nambah agenda fakultas (Opsional, sesuaikan rolenya)
        if (!$user->isAdmin() && !$user->hasRole('Pegawai')) {
            Http::post("{$this->baseUrl}/sendMessage", ['chat_id' => $chatId, 'text' => "❌ Anda tidak memiliki akses menambah Agenda Fakultas."]);
            return;
        }

        $rawInput = trim(substr($text, 11)); // potong '/addagenda '
        if (empty($rawInput)) return;

        $title = $rawInput;
        $tanggalMulai = Carbon::today();

        if (str_contains($rawInput, '@')) {
            $parts = explode('@', $rawInput);
            $title = trim($parts[0]);
            $timeStr = strtolower(trim($parts[1]));
            
            $parsedDate = $this->parseSmartDate($timeStr);
            if ($parsedDate) {
                $tanggalMulai = $parsedDate;
            }
        }

        AgendaFakultas::create([
            'judul' => $title,
            'kategori' => 'Lainnya', // Default
            'warna' => '#3b82f6',    // Default biru
            'tanggal_mulai' => $tanggalMulai->format('Y-m-d'),
            'is_all_day' => true,
            'tampil_di_signage' => true,
            'created_by' => $user->id,
        ]);

        $msg = "🏛️✅ <b>Agenda Fakultas berhasil ditambahkan!</b>\nDiset untuk tanggal: <b>" . $tanggalMulai->translatedFormat('d M Y') . "</b>";
        Http::post("{$this->baseUrl}/sendMessage", ['chat_id' => $chatId, 'text' => $msg, 'parse_mode' => 'HTML']);
    }

    /**
     * FITUR: Menampilkan Kegiatan/Peminjaman Ruang Hari Ini
     */
    private function handleListKegiatan($chatId)
    {
        $today = Carbon::today()->format('Y-m-d');
        
        // Ambil kegiatan yang disetujui dan berlangsung hari ini
        $kegiatans = Kegiatan::with(['ruangan', 'user'])
                    ->where('status', 'disetujui')
                    ->whereDate('waktu_mulai', $today)
                    ->orderBy('waktu_mulai', 'asc')
                    ->get();

        if ($kegiatans->isEmpty()) {
            Http::post("{$this->baseUrl}/sendMessage", ['chat_id' => $chatId, 'text' => "🏢 Tidak ada jadwal kegiatan / pemakaian ruang hari ini."]);
            return;
        }

        $msg = "🏢 <b>Jadwal Kegiatan Hari Ini:</b>\n\n";
        foreach ($kegiatans as $keg) {
            $jam = Carbon::parse($keg->waktu_mulai)->format('H:i') . " - " . Carbon::parse($keg->waktu_selesai)->format('H:i');
            $ruang = $keg->ruangan ? $keg->ruangan->nama : 'Ruang tdk diketahui';
            $pic = explode(' ', $keg->nama_pic ?? ($keg->user->name ?? 'Anonim'))[0]; // Ambil nama depan saja
            
            $msg .= "📍 <b>{$ruang}</b> ({$jam})\n";
            $msg .= "└ {$keg->nama_kegiatan} <i>(PIC: {$pic})</i>\n\n";
        }

        Http::post("{$this->baseUrl}/sendMessage", ['chat_id' => $chatId, 'text' => $msg, 'parse_mode' => 'HTML']);
    }

    /**
     * FITUR: Booking Ruangan via Telegram
     * Format: /book [Judul] @[Nama Ruang] @[Waktu Mulai]-[Waktu Selesai]
     */
    private function handleBookCommand($chatId, $text, EventService $eventService)
    {
        $user = User::where('telegram_chat_id', (string)$chatId)->first();
        if (!$user) { $this->replyUnregistered($chatId); return; }

        $rawInput = trim(substr($text, 6)); // Potong '/book '
        
        // Validasi format (harus ada minimal 2 buah '@')
        if (empty($rawInput) || substr_count($rawInput, '@') < 2) {
            $msg = "❌ <b>Format Booking Salah!</b>\nGunakan format:\n<code>/book [Acara] @[Ruang] @[Waktu] [opsional: untuk Nama]</code>\n\nContoh Biasa:\n<code>/book Rapat Pimpinan @GC-701 @besok 09:00-11:00</code>\n\nContoh Pesankan Orang Lain:\n<code>/book Rapat Prodi @GC-602 @lusa 13:00-15:00 untuk Budi</code>";
            Http::post("{$this->baseUrl}/sendMessage", ['chat_id' => $chatId, 'text' => $msg, 'parse_mode' => 'HTML']);
            return;
        }

        // Pecah berdasarkan '@'
        $parts = explode('@', $rawInput);
        $title = trim($parts[0]);
        $ruangName = trim($parts[1]);
        
        $timeStrOriginal = trim($parts[2]); 
        $timeStr = strtolower($timeStrOriginal);
        $targetUser = $user; // Default peminjam adalah diri sendiri

        // =======================================================
        // FITUR BARU: Delegasi Pemohon (Keyword: "untuk [nama]")
        // =======================================================
        if (str_contains($timeStr, 'untuk ')) {
            $splitUntuk = explode('untuk ', $timeStr);
            $timeStr = trim($splitUntuk[0]); // Sisa format waktu (misal: "besok 09:00-11:00")
            $targetName = trim($splitUntuk[1]); // Nama target (misal: "budi")

            // Cek apakah user yang ngetik command punya hak mendelegasikan
            if ($user->isAdmin() || $user->hasRole('Pegawai')) {
                // Cari target di database berdasarkan nama
                $foundUser = \App\Models\User::where('name', 'like', "%{$targetName}%")->first();
                
                if ($foundUser) {
                    $targetUser = $foundUser;
                } else {
                    Http::post("{$this->baseUrl}/sendMessage", ['chat_id' => $chatId, 'text' => "❌ Pegawai/Dosen dengan nama mengandung kata <b>'{$targetName}'</b> tidak ditemukan di database."]);
                    return;
                }
            } else {
                Http::post("{$this->baseUrl}/sendMessage", ['chat_id' => $chatId, 'text' => "❌ Anda tidak memiliki hak akses untuk meminjamkan ruangan atas nama orang lain."]);
                return;
            }
        }

        // 1. CARI RUANGAN BERDASARKAN NAMA
        $ruangan = \App\Models\Ruangan::where('nama', 'like', "%{$ruangName}%")->first();
        if (!$ruangan) {
            Http::post("{$this->baseUrl}/sendMessage", ['chat_id' => $chatId, 'text' => "❌ Ruangan mengandung kata <b>'{$ruangName}'</b> tidak ditemukan di sistem."]);
            return;
        }

        // 2. PARSING WAKTU MULAI & SELESAI
        $timeParts = explode('-', $timeStr);
        $startStr = trim($timeParts[0]); 
        $endStr = isset($timeParts[1]) ? trim($timeParts[1]) : null; 

        $waktuMulai = $this->parseSmartDate($startStr);
        if (!$waktuMulai) {
            Http::post("{$this->baseUrl}/sendMessage", ['chat_id' => $chatId, 'text' => "❌ Format tanggal/waktu mulai tidak dikenali."]);
            return;
        }

        $waktuSelesai = $waktuMulai->copy()->addHours(2); // Default 2 jam jika tidak diset jam pulangnya
        if ($endStr) {
            $endStr = str_replace('.', ':', $endStr); 
            try {
                $waktuSelesai = Carbon::parse($waktuMulai->format('Y-m-d') . ' ' . $endStr);
            } catch (\Exception $e) {}
        }

        if ($waktuSelesai->lte($waktuMulai)) {
            Http::post("{$this->baseUrl}/sendMessage", ['chat_id' => $chatId, 'text' => "❌ Waktu selesai harus lebih dari waktu mulai."]);
            return;
        }

        // 3. CEK BENTROK VIA EVENT SERVICE
        $requestData = [
            'waktu_mulai' => $waktuMulai->format('Y-m-d H:i:s'),
            'waktu_selesai' => $waktuSelesai->format('Y-m-d H:i:s'),
            'ruangan_id' => $ruangan->id,
            'tipe_berulang' => 'harian', 
        ];

        $bentrok = $eventService->isRoomTaken($requestData);

        if ($bentrok) {
            $saran = $eventService->getSuggestedRooms($requestData, $ruangan->kapasitas ?? 0);
            
            $msg = "⚠️ <b>MAAF, RUANGAN BENTROK!</b>\n\n";
            $msg .= "Ruang <b>{$ruangan->nama}</b> tidak tersedia karena sedang dipakai:\n";
            $msg .= "📌 <b>{$bentrok->nama_kegiatan}</b>\n\n";

            if ($saran->isNotEmpty()) {
                $msg .= "💡 <b>Saran Ruangan Alternatif (Kosong):</b>\n";
                foreach ($saran->take(3) as $s) {
                    $msg .= "- {$s->nama} (Kapasitas: {$s->kapasitas})\n";
                }
            } else {
                $msg .= "<i>Sayangnya tidak ada ruangan lain yang kosong di jam tersebut.</i>";
            }

            Http::post("{$this->baseUrl}/sendMessage", ['chat_id' => $chatId, 'text' => $msg, 'parse_mode' => 'HTML']);
            return;
        }

        // 4. JIKA AMAN, SIMPAN KE DATABASE
        $status = ($user->isAdmin()) ? 'disetujui' : 'belum_disetujui';
        
        $kegiatan = Kegiatan::create([
            'ruangan_id' => $ruangan->id,
            'nama_kegiatan' => $title,
            'jenis_kegiatan' => 'Lainnya',
            'waktu_mulai' => $waktuMulai->format('Y-m-d H:i:s'),
            'waktu_selesai' => $waktuSelesai->format('Y-m-d H:i:s'),
            'user_id' => $targetUser->id,       // Menggunakan target User (Bisa diri sendiri atau orang yang didelegasikan)
            'status' => $status,
            'nama_pic' => $targetUser->name,    // Nama penanggung jawabnya
            'deskripsi' => "Dipesan melalui Telegram Bot oleh {$user->name}",
        ]);

        \App\Models\KegiatanHistory::create([
            'kegiatan_id' => $kegiatan->id,
            'user_id' => $user->id,
            'action' => 'created',
            'note' => 'Booking via Telegram',
            'created_at' => now(),
        ]);

        $statusText = ($status === 'disetujui') ? "✅ <b>Disetujui Otomatis</b>" : "⏳ <b>Menunggu Verifikasi</b>";

        $msg = "🏢 <b>BOOKING BERHASIL DICATAT!</b>\n\n";
        $msg .= "📌 <b>Kegiatan:</b> {$title}\n";
        $msg .= "📍 <b>Ruangan:</b> {$ruangan->nama}\n";
        if ($targetUser->id !== $user->id) {
            $msg .= "👤 <b>Pemohon:</b> {$targetUser->name} <i>(Dipesankan oleh Anda)</i>\n";
        }
        $msg .= "🕒 <b>Waktu:</b> " . $waktuMulai->translatedFormat('d M Y, H:i') . " s/d " . $waktuSelesai->format('H:i') . " WIB\n";
        $msg .= "Status: " . $statusText;

        // Kirim konfirmasi ke Admin/Orang yang ngetik command
        Http::post("{$this->baseUrl}/sendMessage", ['chat_id' => $chatId, 'text' => $msg, 'parse_mode' => 'HTML']);

        // NOTIFIKASI KE TARGET USER (JIKA DIPESANKAN OLEH ORANG LAIN & PUNYA TELEGRAM)
        if ($targetUser->id !== $user->id && !empty($targetUser->telegram_chat_id)) {
            $notifMsg = "🏢 <b>ANDA DIDAFTARKAN PEMINJAMAN RUANG</b>\n\n";
            $notifMsg .= "Sistem mendeteksi <b>{$user->name}</b> baru saja memesankan ruangan atas nama Anda.\n\n";
            $notifMsg .= "📌 <b>Kegiatan:</b> {$title}\n";
            $notifMsg .= "📍 <b>Ruang:</b> {$ruangan->nama}\n";
            $notifMsg .= "🕒 <b>Waktu:</b> " . $waktuMulai->translatedFormat('d M Y, H:i') . " - " . $waktuSelesai->format('H:i') . " WIB\n\n";
            $notifMsg .= "Silakan cek Dashboard FTMM-Nexus untuk melengkapi dokumen pendukung.";
            
            Http::post("{$this->baseUrl}/sendMessage", ['chat_id' => $targetUser->telegram_chat_id, 'text' => $notifMsg, 'parse_mode' => 'HTML']);
        }
    }
}