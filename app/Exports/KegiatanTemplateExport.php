<?php

namespace App\Exports;

use App\Models\Ruangan;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;

class KegiatanTemplateExport implements WithEvents, WithTitle
{
    // ── Konstanta jenis kegiatan (harus sinkron dengan Import & Blade) ──
    private const JENIS_KEGIATAN = [
        'UTS',
        'UAS',
        'Kegiatan Ormawa',
        'Seminar Proposal',
        'Sidang Skripsi',
        'Rapat',
        'Lomba',
        'PHL',
        'Kuliah Tamu',
        'Lainnya',
    ];

    // ── Definisi kolom: [key, label_header, lebar, wajib, keterangan] ──
    private const COLUMNS = [
        ['judul_kegiatan', 'judul_kegiatan *',  30, true,  'Contoh: UTS Pemrograman Web'],
        ['jenis_kegiatan', 'jenis_kegiatan *',  20, true,  'Pilih dari dropdown'],
        ['ruangan',        'ruangan *',          20, true,  'Pilih dari dropdown'],
        ['tanggal',        'tanggal *',          14, true,  'Format: YYYY-MM-DD'],
        ['jam_mulai',      'jam_mulai *',        12, true,  'Contoh: 08:00'],
        ['jam_selesai',    'jam_selesai *',      12, true,  'Contoh: 10:00'],
        ['pengawas',       'pengawas',           24, false, 'Khusus UTS / UAS'],
        ['nama_pic',       'nama_pic',           24, false, 'Nama penanggung jawab'],
        ['no_hp',          'no_hp',              18, false, 'Contoh: 08123456789'],
        ['pembimbing_1',   'pembimbing_1',       24, false, 'Khusus Seminar Proposal / Sidang Skripsi'],
        ['pembimbing_2',   'pembimbing_2',       24, false, 'Khusus Seminar Proposal / Sidang Skripsi'],
        ['penguji_1',      'penguji_1',          24, false, 'Khusus Seminar Proposal / Sidang Skripsi'],
        ['penguji_2',      'penguji_2',          24, false, 'Khusus Sidang Skripsi'],
    ];

    // Baris data mulai dari row ini (1=judul, 2=subheader, 3=header kolom, 4=keterangan, 5+=data)
    private const DATA_START_ROW = 5;
    private const DATA_END_ROW   = 204; // support 200 baris input

    public function title(): string
    {
        return 'Import Kegiatan';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $wb = $event->sheet->getDelegate()->getParent();
                $ws = $event->sheet->getDelegate();

                // ── Ambil data ruangan dari DB ───────────────────
                $ruanganList = Ruangan::where('is_active', 1)
                    ->orderBy('nama')
                    ->pluck('nama')
                    ->toArray();

                // ── Buat sheet referensi tersembunyi ─────────────
                $refSheet = $wb->createSheet();
                $refSheet->setTitle('_Referensi');

                // Isi jenis kegiatan di kolom A
                $refSheet->setCellValue('A1', 'jenis_kegiatan');
                foreach (self::JENIS_KEGIATAN as $i => $jenis) {
                    $refSheet->setCellValueByColumnAndRow(1, $i + 2, $jenis);
                }
                $jenisEndRow = count(self::JENIS_KEGIATAN) + 1;

                // Isi ruangan di kolom B
                $refSheet->setCellValue('B1', 'ruangan');
                foreach ($ruanganList as $i => $nama) {
                    $refSheet->setCellValueByColumnAndRow(2, $i + 2, $nama);
                }
                $ruanganEndRow = count($ruanganList) + 1;

                // Sembunyikan sheet referensi
                $refSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);

                // ── Kembali ke sheet utama ───────────────────────
                $wb->setActiveSheetIndex(0);

                // ── Warna ────────────────────────────────────────
                $colorNavy    = '1E3A5F';
                $colorSlate   = '334155';
                $colorWhite   = 'FFFFFF';
                $colorYellow  = 'FFF3CD'; // wajib
                $colorBlue    = 'EFF6FF'; // opsional / info
                $colorGray    = 'F8FAFC'; // keterangan
                $colorMuted   = '6B7280';
                $colorAccent  = 'DBEAFE'; // pengawas UTS/UAS

                // ── Helper style border ──────────────────────────
                $borderThin = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['argb' => 'FFD1D5DB'],
                        ],
                    ],
                ];
                $borderMedium = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color'       => ['argb' => 'FF94A3B8'],
                        ],
                    ],
                ];

                $totalCols  = count(self::COLUMNS);
                $lastColLtr = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalCols);

                // ════════════════════════════════════════════════
                // ROW 1 — Judul besar
                // ════════════════════════════════════════════════
                $ws->mergeCells("A1:{$lastColLtr}1");
                $ws->setCellValue('A1', 'TEMPLATE IMPORT KEGIATAN — Sistem Peminjaman Ruang');
                $ws->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 13, 'color' => ['argb' => 'FF'.$colorWhite], 'name' => 'Arial'],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF'.$colorNavy]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $ws->getRowDimension(1)->setRowHeight(28);

                // ════════════════════════════════════════════════
                // ROW 2 — Subheader keterangan warna
                // ════════════════════════════════════════════════
                $ws->mergeCells("A2:{$lastColLtr}2");
                $ws->setCellValue('A2', '★ Kolom bertanda * = WAJIB diisi  |  Pilih dari dropdown untuk kolom Jenis & Ruangan  |  Jangan ubah nama kolom di baris 3');
                $ws->getStyle('A2')->applyFromArray([
                    'font'      => ['italic' => true, 'size' => 9, 'color' => ['argb' => 'FF374151'], 'name' => 'Arial'],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE0F2FE']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $ws->getRowDimension(2)->setRowHeight(18);

                // ════════════════════════════════════════════════
                // ROW 3 — Header kolom (nama key = heading row import)
                // ════════════════════════════════════════════════
                $ws->getRowDimension(3)->setRowHeight(36);
                foreach (self::COLUMNS as $colIdx => [$key, $label, $width, $required, $notes]) {
                    $colNum = $colIdx + 1;
                    $colLtr = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colNum);

                    // Header
                    $ws->setCellValueByColumnAndRow($colNum, 3, $label);
                    $ws->getStyleByColumnAndRow($colNum, 3)->applyFromArray([
                        'font'      => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FF'.$colorWhite], 'name' => 'Arial'],
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF'.($required ? $colorNavy : $colorSlate)]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                        'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF94A3B8']]],
                    ]);

                    // Lebar kolom
                    $ws->getColumnDimension($colLtr)->setWidth($width);
                }

                // ════════════════════════════════════════════════
                // ROW 4 — Baris keterangan/hint
                // ════════════════════════════════════════════════
                $ws->getRowDimension(4)->setRowHeight(18);
                foreach (self::COLUMNS as $colIdx => [$key, $label, $width, $required, $notes]) {
                    $colNum = $colIdx + 1;
                    $ws->setCellValueByColumnAndRow($colNum, 4, $notes);
                    $ws->getStyleByColumnAndRow($colNum, 4)->applyFromArray([
                        'font'      => ['italic' => true, 'size' => 8, 'color' => ['argb' => 'FF'.$colorMuted], 'name' => 'Arial'],
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF8FAFC']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                        'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD1D5DB']]],
                    ]);
                }

                // ════════════════════════════════════════════════
                // ROW 5-8 — Baris contoh data
                // ════════════════════════════════════════════════
                $examples = [
                    // UTS
                    ['UTS Pemrograman Web',           'UTS',           $ruanganList[0] ?? 'GC-6.01', '2026-06-02', '08:00', '10:00', 'Dr. Ahmad Fauzi',    'Koordinator UTS', '081234567890', null, null, null, null],
                    // UAS
                    ['UAS Basis Data',                'UAS',           $ruanganList[1] ?? 'GC-7.02', '2026-07-15', '10:00', '12:00', 'Dr. Siti Rahayu',    'Koordinator UAS', '089876543210', null, null, null, null],
                    // Sidang Skripsi
                    ['Sidang Skripsi: Budi Santoso',  'Sidang Skripsi',$ruanganList[2] ?? 'GC-6.02', '2026-06-10', '09:00', '11:00', null,                 'Budi Santoso',    '082111222333', 'Prof. Dr. A, M.T.', 'Dr. B, Ph.D.', 'Dr. C, M.Sc.', 'Dr. D, M.T.'],
                    // Kegiatan Ormawa
                    ['Seminar Kewirausahaan Digital', 'Kegiatan Ormawa',$ruanganList[3] ?? 'Aula Lt. 8','2026-06-20','13:00','16:00', null,                'BEM FT',          '085333444555', null, null, null, null],
                ];

                foreach ($examples as $rowOffset => $exRow) {
                    $rowNum  = self::DATA_START_ROW + $rowOffset;
                    $isUjian = in_array($exRow[1], ['UTS', 'UAS']);
                    $ws->getRowDimension($rowNum)->setRowHeight(20);

                    foreach ($exRow as $colIdx => $val) {
                        $colNum  = $colIdx + 1;
                        $key     = self::COLUMNS[$colIdx][0];
                        $required = self::COLUMNS[$colIdx][3];

                        $ws->setCellValueByColumnAndRow($colNum, $rowNum, $val);
                        $bgColor = 'FFFFFFFF';
                        if ($val !== null) {
                            if ($isUjian && $key === 'pengawas') {
                                $bgColor = 'FF'.$colorAccent;
                            } elseif ($required) {
                                $bgColor = 'FF'.$colorYellow;
                            } else {
                                $bgColor = 'FFF0F4F8';
                            }
                        }
                        $ws->getStyleByColumnAndRow($colNum, $rowNum)->applyFromArray([
                            'font'      => ['size' => 10, 'name' => 'Arial'],
                            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bgColor]],
                            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD1D5DB']]],
                        ]);
                    }
                }

                // ════════════════════════════════════════════════
                // ROW 9–204 — Baris input kosong
                // ════════════════════════════════════════════════
                $emptyStart = self::DATA_START_ROW + count($examples);
                for ($rowNum = $emptyStart; $rowNum <= self::DATA_END_ROW; $rowNum++) {
                    $ws->getRowDimension($rowNum)->setRowHeight(20);
                    foreach (self::COLUMNS as $colIdx => [$key, $label, $width, $required, $notes]) {
                        $colNum = $colIdx + 1;
                        $ws->getStyleByColumnAndRow($colNum, $rowNum)->applyFromArray([
                            'font'    => ['size' => 10, 'name' => 'Arial'],
                            'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $required ? 'FFFFFFF7' : 'FFFFFFFF']],
                            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD1D5DB']]],
                        ]);
                    }
                }

                // ════════════════════════════════════════════════
                // DATA VALIDATION — Dropdown Jenis Kegiatan (kolom B)
                // ════════════════════════════════════════════════
                for ($row = self::DATA_START_ROW; $row <= self::DATA_END_ROW; $row++) {
                    $cell = $ws->getCell("B{$row}");
                    $validation = $cell->getDataValidation(); // ⬅️ BUKAN set langsung

                    $validation->setType(DataValidation::TYPE_LIST)
                        ->setErrorStyle(DataValidation::STYLE_STOP)
                        ->setAllowBlank(true)
                        ->setShowErrorMessage(true)
                        ->setErrorTitle('Jenis tidak valid')
                        ->setError('Pilih dari daftar yang tersedia.')
                        ->setFormula1("='_Referensi'!\$A\$2:\$A\${$jenisEndRow}");
                }

                // ════════════════════════════════════════════════
                // DATA VALIDATION — Dropdown Ruangan (kolom C)
                // ════════════════════════════════════════════════
                for ($row = self::DATA_START_ROW; $row <= self::DATA_END_ROW; $row++) {
                    $cell = $ws->getCell("C{$row}");
                    $validation = $cell->getDataValidation();

                    $validation->setType(DataValidation::TYPE_LIST)
                        ->setErrorStyle(DataValidation::STYLE_STOP)
                        ->setAllowBlank(true)
                        ->setShowErrorMessage(true)
                        ->setErrorTitle('Ruangan tidak valid')
                        ->setError('Pilih ruangan dari daftar yang tersedia.')
                        ->setFormula1("='_Referensi'!\$B\$2:\$B\${$ruanganEndRow}");
                }

                // ════════════════════════════════════════════════
                // FREEZE PANES — beku di row 5 (header tetap terlihat)
                // ════════════════════════════════════════════════
                $ws->freezePane('A5');

                // ════════════════════════════════════════════════
                // LEGENDA di kolom O (setelah kolom terakhir + 1 kosong)
                // ════════════════════════════════════════════════
                $legendCol = $totalCols + 2;
                $legendColLtr = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($legendCol);
                $ws->getColumnDimension($legendColLtr)->setWidth(34);

                $legends = [
                    [3,  'LEGENDA',                              $colorNavy,  $colorWhite, true],
                    [4,  '★ Kolom wajib (background kuning)',    'FFF3CD',    '374151',    false],
                    [5,  '  Kolom opsional (background putih)',  'F8FAFC',    '374151',    false],
                    [6,  '  Pengawas khusus UTS/UAS (biru)',     'DBEAFE',    '1D4ED8',    false],
                    [8,  'ATURAN RUANGAN',                       $colorSlate, $colorWhite, true],
                    [9,  'Nama ruangan HARUS sama persis',       'F8FAFC',    '374151',    false],
                    [10, 'dengan data di database sistem.',      'F8FAFC',    '374151',    false],
                    [11, 'Gunakan dropdown kolom C.',            'F8FAFC',    '374151',    false],
                    [13, 'FORMAT TANGGAL & JAM',                 $colorSlate, $colorWhite, true],
                    [14, 'Tanggal : YYYY-MM-DD',                'F8FAFC',    '374151',    false],
                    [15, 'Jam     : HH:MM',                     'F8FAFC',    '374151',    false],
                    [16, 'Contoh  : 2026-06-02 | 08:00',        'F8FAFC',    '374151',    false],
                    [18, 'JENIS KEGIATAN TERSEDIA',              $colorSlate, $colorWhite, true],
                ];

                // Tambahkan list jenis kegiatan ke legenda
                foreach (self::JENIS_KEGIATAN as $i => $jenis) {
                    $legends[] = [19 + $i, '  • ' . $jenis, 'F8FAFC', '374151', false];
                }

                foreach ($legends as [$row, $text, $bg, $fg, $bold]) {
                    $cell = $ws->getCellByColumnAndRow($legendCol, $row);
                    $cell->setValue($text);
                    $ws->getStyleByColumnAndRow($legendCol, $row)->applyFromArray([
                        'font'      => ['bold' => $bold, 'size' => 9, 'color' => ['argb' => 'FF'.$fg], 'name' => 'Arial'],
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF'.$bg]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
                        'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD1D5DB']]],
                    ]);
                }

                // ── Tab warna ────────────────────────────────────
                $ws->getTabColor()->setRGB($colorNavy);
                $refSheet->getTabColor()->setRGB('94A3B8');
            },
        ];
    }
}