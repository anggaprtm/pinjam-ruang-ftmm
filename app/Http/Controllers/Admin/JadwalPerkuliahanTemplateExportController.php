<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ruangan;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use Illuminate\Support\Facades\Response;

class JadwalPerkuliahanTemplateExportController extends Controller
{
    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header
        $headers = ['nama_ruangan', 'mata_kuliah', 'dosen', 'hari', 'waktu_mulai', 'waktu_selesai', 'berlaku_mulai', 'berlaku_sampai', 'tipe', 'program_studi'];
        $sheet->fromArray($headers, null, 'A1');

        // Dropdown options
        $hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $tipe = ['Kuliah Reguler', 'Seminar Proposal', 'Seminar Hasil', 'PHL'];
        $prodi = ['TI', 'TRKB', 'TSD', 'TE', 'RN'];
        $ruangan = Ruangan::pluck('nama')->toArray();

        // Apply dropdowns to first 100 rows
        for ($row = 2; $row <= 101; $row++) {
            $this->addDropdown($sheet, "A{$row}", $ruangan); // nama_ruangan
            $this->addDropdown($sheet, "D{$row}", $hari);    // hari
            $this->addDropdown($sheet, "I{$row}", $tipe);    // tipe
            $this->addDropdown($sheet, "J{$row}", $prodi);   // program_studi
        }

        // Export
        $filename = 'template_jadwal_perkuliahan.xlsx';
        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), $filename);
        $writer->save($tempFile);

        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }

    private function addDropdown($sheet, $cell, $options)
    {
        $validation = $sheet->getCell($cell)->getDataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_STOP);
        $validation->setAllowBlank(true);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setFormula1('"' . implode(',', $options) . '"');
    }
}