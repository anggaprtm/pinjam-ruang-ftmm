<?php

namespace App\Imports;

use App\Models\Kegiatan;
use App\Models\Ruangan;
use App\Models\KegiatanHistory;
use App\Services\EventService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Validator;

class KegiatanImport implements ToCollection, WithHeadingRow
{
    private array $rowErrors = [];
    private EventService $eventService;

    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
    }

    public function headingRow(): int
    {
        return 3;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 4;

            // ── CEGAH BACA SHEET REFERENSI ─────────────────────────
            // Sheet Referensi tidak punya kolom 'judul_kegiatan'. Kalau tidak ada, skip!
            if (!isset($row['judul_kegiatan'])) {
                continue;
            }

            // ── SKIP BARIS 4 (Keterangan/Hint) ─────────────────────
            if ($rowNumber === 4) {
                continue;
            }

            // ── SKIP BARIS KOSONG ──────────────────────────────────
            if (empty(trim($row['judul_kegiatan'] ?? '')) && empty(trim($row['ruangan'] ?? ''))) {
                continue;
            }

            // ── 1. Validasi Input Dasar ───────────────────────────
            $validator = Validator::make($row->toArray(), [
                'judul_kegiatan' => 'required|string',
                'ruangan'        => 'required|string',
                'tanggal'        => 'required',
                'jam_mulai'      => 'required',
                'jam_selesai'    => 'required',
                'jenis_kegiatan' => 'required|in:Kegiatan Ormawa,Seminar Proposal,Sidang Skripsi,Rapat,Lomba,PHL,Kuliah Tamu,Lainnya,UTS,UAS',
            ], [
                'judul_kegiatan.required' => 'Kolom judul_kegiatan wajib diisi.',
                'ruangan.required'        => 'Kolom ruangan wajib diisi.',
                'tanggal.required'        => 'Kolom tanggal wajib diisi.',
                'jam_mulai.required'      => 'Kolom jam_mulai wajib diisi.',
                'jam_selesai.required'    => 'Kolom jam_selesai wajib diisi.',
                'jenis_kegiatan.required' => 'Kolom jenis_kegiatan wajib diisi.',
                'jenis_kegiatan.in'       => 'Jenis kegiatan tidak valid.',
            ]);

            if ($validator->fails()) {
                $this->rowErrors[] = "Baris {$rowNumber}: " . implode(', ', $validator->errors()->all());
                continue; 
            }

            try {
                // ── 2. Validasi & Cari Ruangan ─────────────────────
                $namaRuangan = trim($row['ruangan']);
                $ruangan = Ruangan::where('nama', $namaRuangan)->where('is_active', 1)->first();

                if (!$ruangan) {
                    throw new \Exception("Ruangan '{$namaRuangan}' tidak ditemukan atau sedang tidak aktif.");
                }

                // ── 3. Parse Tanggal & Jam ─────────────────────────
                $tanggalRaw = $row['tanggal'];
                $formatTanggal = is_numeric($tanggalRaw) 
                    ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($tanggalRaw)->format('Y-m-d') 
                    : Carbon::parse(trim($tanggalRaw))->format('Y-m-d');

                $jamMulai = is_numeric($row['jam_mulai']) 
                    ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['jam_mulai'])->format('H:i') 
                    : Carbon::parse(trim($row['jam_mulai']))->format('H:i');

                $jamSelesai = is_numeric($row['jam_selesai']) 
                    ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['jam_selesai'])->format('H:i') 
                    : Carbon::parse(trim($row['jam_selesai']))->format('H:i');

                $waktuMulai   = Carbon::parse($formatTanggal . ' ' . $jamMulai);
                $waktuSelesai = Carbon::parse($formatTanggal . ' ' . $jamSelesai);

                if ($waktuSelesai->lte($waktuMulai)) {
                    throw new \Exception("Jam selesai harus lebih besar dari jam mulai.");
                }

                // ── 4. Cek Bentrok via EventService ────────────────
                $payloadCekBentrok = [
                    'ruangan_id'    => $ruangan->id,
                    'waktu_mulai'   => $waktuMulai->format('Y-m-d H:i:s'),
                    'waktu_selesai' => $waktuSelesai->format('Y-m-d H:i:s'),
                ];

                $kegiatanBentrok = $this->eventService->isRoomTaken($payloadCekBentrok);
                if ($kegiatanBentrok) {
                    throw new \Exception("Jadwal bentrok dengan kegiatan: '{$kegiatanBentrok->nama_kegiatan}'.");
                }

                // ── 5. Setup Status Berdasarkan Role ───────────────
                $status = 'disetujui'; // Default Admin
                if (auth()->check() && (auth()->user()->hasRole('User'))) {
                    $status = 'belum_disetujui';
                }

                $jenis = trim($row['jenis_kegiatan']);
                $isAkademik = in_array($jenis, ['Seminar Proposal', 'Sidang Skripsi']);
                $pengawas = in_array($jenis, ['UTS', 'UAS']) ? (trim($row['pengawas'] ?? '') ?: null) : null;

                // ── 6. Simpan Data ─────────────────────────────────
                $kegiatan = Kegiatan::create([
                    'nama_kegiatan'      => trim($row['judul_kegiatan']),
                    'deskripsi'          => 'Diimport via Excel',
                    'jenis_kegiatan'     => $jenis,
                    'dosen_pembimbing_1' => $isAkademik ? (trim($row['pembimbing_1'] ?? '') ?: null) : null,
                    'dosen_pembimbing_2' => $isAkademik ? (trim($row['pembimbing_2'] ?? '') ?: null) : null,
                    'dosen_penguji_1'    => $isAkademik ? (trim($row['penguji_1'] ?? '')    ?: null) : null,
                    'dosen_penguji_2'    => $isAkademik ? (trim($row['penguji_2'] ?? '')    ?: null) : null,
                    'pengawas'           => $pengawas,
                    'ruangan_id'         => $ruangan->id,
                    'waktu_mulai'        => $waktuMulai,
                    'waktu_selesai'      => $waktuSelesai,
                    'nama_pic'           => trim($row['nama_pic'] ?? '') ?: null,
                    'nomor_telepon'      => trim($row['no_hp'] ?? '') ?: '08000000000',
                    'user_id'            => auth()->id(),
                    'status'             => $status,
                ]);

                KegiatanHistory::create([
                    'kegiatan_id' => $kegiatan->id,
                    'user_id'     => auth()->id(),
                    'action'      => 'created',
                    'note'        => 'Diimport massal via Excel',
                    'meta'        => json_encode(['borrower_id' => auth()->id()]),
                    'created_at'  => now(),
                ]);

            } catch (\Exception $e) {
                $this->rowErrors[] = "Baris {$rowNumber}: " . $e->getMessage();
            }
        }
    }

    public function getRowErrors(): array
    {
        return $this->rowErrors;
    }
}