<?php

namespace App\Imports;

use App\Models\OrmawaProgramItem;
use App\Models\OrmawaProgramPlan;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class OrmawaProgramItemsImport implements ToCollection, WithHeadingRow
{
    public function __construct(private readonly OrmawaProgramPlan $plan)
    {
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $nama = trim((string) ($row['nama_rencana'] ?? ''));
            if ($nama === '') {
                continue;
            }

            $payload = [
                'nama_rencana' => $nama,
                'timeline_mulai_rencana' => $this->normalizeDate($row['timeline_mulai_rencana'] ?? null),
                'timeline_selesai_rencana' => $this->normalizeDate($row['timeline_selesai_rencana'] ?? null),
                'deskripsi_rencana' => $row['deskripsi_rencana'] ?? null,
                'status_item' => in_array(($row['status_item'] ?? ''), ['belum_diajukan', 'diajukan', 'proses', 'sik_terbit', 'ditolak', 'arsip'], true)
                    ? $row['status_item']
                    : 'belum_diajukan',
            ];

            $kode = trim((string) ($row['kode_proker'] ?? ''));
            if ($kode !== '') {
                OrmawaProgramItem::updateOrCreate(
                    ['plan_id' => $this->plan->id, 'kode_proker' => $kode],
                    $payload + ['kode_proker' => $kode]
                );
            } else {
                $this->plan->items()->create($payload + ['kode_proker' => null]);
            }
        }
    }

    private function normalizeDate($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
            }

            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }
}
