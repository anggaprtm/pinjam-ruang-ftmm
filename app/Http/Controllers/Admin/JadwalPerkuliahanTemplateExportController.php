<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ruangan;
use App\Exports\JadwalPerkuliahanTemplateExport; // Pastikan import ini ada
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use Maatwebsite\Excel\Facades\Excel; // Pastikan import facade Excel ada
use Illuminate\Support\Facades\Response;

class JadwalPerkuliahanTemplateExportController extends Controller
{
    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header BARU (Sesuai Logic Import)
        // Urutan: A, B, C, D, E, F, G, H, I
        $headers = [
            'nama_ruangan',   // A
            'kode_matkul',    // B (Baru)
            'mata_kuliah',    // C
            'dosen',          // D
            'hari',           // E
            'waktu_mulai',    // F
            'waktu_selesai',  // G
            'tipe',           // H
            'program_studi'   // I
        ];
        
        $sheet->fromArray($headers, null, 'A1');

        // Dropdown options
        $hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $tipe = ['Kuliah Reguler', 'Seminar Proposal', 'Seminar Hasil', 'PHL'];
        $prodi = ['TI', 'TRKB', 'TSD', 'TE', 'RN'];
        
        // Ambil nama ruangan (Pluck array)
        $ruangan = Ruangan::pluck('nama')->toArray();

        // Apply dropdowns to first 100 rows
        for ($row = 2; $row <= 101; $row++) {
            // Kolom A: Nama Ruangan
            $this->addDropdown($sheet, "A{$row}", $ruangan); 
            
            // Kolom E: Hari (Tadinya D, geser ke E karena ada kode_matkul)
            $this->addDropdown($sheet, "E{$row}", $hari);    
            
            // Kolom H: Tipe (Tadinya I, geser maju karena tanggal dihapus)
            $this->addDropdown($sheet, "H{$row}", $tipe);    
            
            // Kolom I: Prodi (Tadinya J, geser maju)
            $this->addDropdown($sheet, "I{$row}", $prodi);   
        }

        // Auto size columns biar rapi
        foreach(range('A','I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
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
        // Validasi Excel punya limit karakter list formula (255 chars).
        // Kalau list ruangan banyak, logic ini bisa error/kepotong.
        // Solusi simple: Ambil 20 ruangan pertama saja jika kebanyakan, 
        // atau biarkan user input manual jika list terlalu panjang.
        // Di sini kita coba implode standar dulu.
        
        $optionsString = '"' . implode(',', $options) . '"';
        
        // Cek panjang string (Safety check)
        if (strlen($optionsString) > 255) {
             // Jika terlalu panjang, kita skip dropdown untuk cell ini 
             // atau bisa ganti logic pakai hidden sheet (lebih advance).
             // Untuk sekarang kita return saja biar gak error file corrupt.
             return; 
        }

        $validation = $sheet->getCell($cell)->getDataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_INFORMATION); // Ganti ke Info biar gak nge-block kalau user mau ketik manual
        $validation->setAllowBlank(true);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setFormula1($optionsString);
    }

    public function export()
    {
        return Excel::download(new JadwalPerkuliahanTemplateExport, 'template_jadwal_perkuliahan.xlsx');
    }
}