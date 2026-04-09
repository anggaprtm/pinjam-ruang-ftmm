<?php

namespace App\Imports;

use App\Models\AsetFakultas;
use App\Models\Ruangan;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Str;

class AsetFakultasImport implements
    ToModel,
    WithHeadingRow,
    SkipsOnError,
    WithBatchInserts,
    WithChunkReading
{
    use SkipsErrors;

    // Cache ruangan names to avoid N+1 on large imports
    protected array $ruanganCache = [];

    public function headingRow(): int
    {
        // Row 1 = "Daftar Barang" header (judul), Row 2 = actual column headers
        return 2;
    }

    public function model(array $row): ?AsetFakultas
    {
        // Skip baris kosong atau header yang tidak relevan
        if (empty($row['kode_barang']) || empty($row['nama_barang'])) {
            return null;
        }

        $kode = (string) $row['kode_barang'];

        // Cari ruangan berdasarkan lokasi_text (match nama ruangan, case-insensitive)
        $ruanganId   = null;
        $lokasiText  = $row['lokasi'] ?? null;

        if ($lokasiText) {
            // Format lokasi dari Excel: "C - Gedung NANO :: Nama Ruangan"
            // Ambil bagian setelah "::" sebagai nama ruangan
            $parts       = explode('::', $lokasiText);
            $namaRuangan = isset($parts[1]) ? trim($parts[1]) : trim($lokasiText);

            if (!isset($this->ruanganCache[$namaRuangan])) {
                $ruangan = Ruangan::whereRaw('LOWER(nama) = ?', [strtolower($namaRuangan)])->first();
                $this->ruanganCache[$namaRuangan] = $ruangan?->id;
            }

            $ruanganId = $this->ruanganCache[$namaRuangan];
        }

        // Normalisasi kondisi
        $kondisiRaw = trim($row['kondisi_barang'] ?? 'Baik');
        $kondisiMap = [
            'baik'         => 'Baik',
            'rusak ringan' => 'Rusak Ringan',
            'rusak berat'  => 'Rusak Berat',
        ];
        $kondisi = $kondisiMap[strtolower($kondisiRaw)] ?? 'Baik';

        // MENGAMBIL ANGGARAN DARI EXCEL (Default: DAMAS)
        $anggaranRaw = strtoupper(trim($row['anggaran'] ?? 'DAMAS'));
        $anggaranValid = in_array($anggaranRaw, ['DAMAS', 'HIBAH', 'IKU']) ? $anggaranRaw : 'DAMAS';

        // Upsert: update jika kode_barang sudah ada, insert jika belum
        $aset = AsetFakultas::withTrashed()->where('kode_barang', $kode)->first();

        if ($aset) {
            // Pulihkan jika soft-deleted, lalu update
            if ($aset->trashed()) {
                $aset->restore();
            }
            $aset->update([
                'tahun_aset'  => $row['tahun_asset'] ?? null,
                'nama_barang' => $row['nama_barang'],
                'kondisi'     => $kondisi,
                'anggaran'    => $anggaranValid,
                'merk'        => $row['merk'] ?? null,
                'deskripsi'   => $row['deskripsi'] ?? null,
                'ruangan_id'  => $ruanganId,
                'lokasi_text' => $lokasiText,
            ]);
            return null; // null = sudah di-handle manual, tidak perlu insert baru
        }

        return new AsetFakultas([
            'kode_barang' => $kode,
            'tahun_aset'  => $row['tahun_asset'] ?? null,
            'nama_barang' => $row['nama_barang'],
            'kondisi'     => $kondisi,
            'anggaran'    => $anggaranValid,
            'merk'        => $row['merk'] ?? null,
            'deskripsi'   => $row['deskripsi'] ?? null,
            'ruangan_id'  => $ruanganId,
            'lokasi_text' => $lokasiText,
        ]);
    }

    public function batchSize(): int
    {
        return 200;
    }

    public function chunkSize(): int
    {
        return 200;
    }
}
