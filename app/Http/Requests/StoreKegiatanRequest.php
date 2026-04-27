<?php

namespace App\Http\Requests;

use App\Models\Kegiatan;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class StoreKegiatanRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('kegiatan_create');
    }

    protected function prepareForValidation()
    {
        // Fungsi bantuan untuk translate bulan ID -> EN
        $translateDate = function($dateStr) {
            if (!$dateStr) return $dateStr;
            
            $id = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            $en = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            
            return str_replace($id, $en, $dateStr);
        };

        // Replace request lama dengan yang sudah di-translate
        $this->merge([
            'waktu_mulai'     => $this->has('waktu_mulai') ? $translateDate($this->waktu_mulai) : null,
            'waktu_selesai'   => $this->has('waktu_selesai') ? $translateDate($this->waktu_selesai) : null,
            'berulang_sampai' => $this->has('berulang_sampai') ? $translateDate($this->berulang_sampai) : null,
        ]);
    }

    public function rules()
    {
        return [
            'nama_kegiatan' => [
                'string',
                'required',
            ],
            'ruangan_id' => [
                'required',
                'integer',
                'exists:ruangan,id', 
            ],
            'jenis_kegiatan' => [
                'required', 
                'in:Kegiatan Ormawa,Seminar Proposal,Sidang Skripsi,Rapat,Lomba,PHL,Kuliah Tamu,UTS,UAS,Lainnya' // Sesuaikan opsi mu
            ],
            'poster' => [
                'nullable', 
                'image',        // Harus gambar (jpg, png, dll)
                'max:2048',     // Maksimal 2MB
                'mimes:jpeg,png,jpg,gif,svg'
            ],
            'nama_pic' => [
                'string',
                'required', // atau 'required' jika wajib
            ],
            'dosen_pembimbing_1' => ['nullable', 'string', 'max:255'],
            'dosen_pembimbing_2' => ['nullable', 'string', 'max:255'],
            'dosen_penguji_1'    => ['nullable', 'string', 'max:255'],
            'dosen_penguji_2'    => ['nullable', 'string', 'max:255'],
            'pengawas'           => ['nullable', 'string', 'max:255'],
            'nomor_telepon' => [
                'required',
                // Hanya angka dan harus diawali dengan 0, misal: 08123456789
                'regex:/^0[0-9]+$/',
                'min:9', // asumsi: minimal 9 digit
                'max:15', // asumsi: maksimal 15 digit
            ],
            'waktu_mulai' => [
                'required',
                'date',
            ],
            'waktu_selesai' => [
                'required',
                'date',
            ],
            'user_id' => [
                'nullable',
            ],
            'custom_user_name' => [
                'required_if:user_id,null',
                'string',
                'nullable',
            ],

            'berulang_sampai' => [
                'nullable',
                'date', 
            ],

            'tipe_berulang' => [
                'required_with:berulang_sampai', // Wajib diisi jika 'berulang_sampai' ada isinya
                'string',
                'in:harian,mingguan', // Pastikan nilainya hanya salah satu dari dua ini
            ],
        ];
    }

    /**
     * Customize error messages for validation.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'nomor_telepon.required' => 'Field nomor telepon wajib diisi.',
            'ruangan_id.required' => 'Ruangan harus dipilih terlebih dahulu.',
            'nomor_telepon.regex' => 'Nomor telepon harus berupa angka dan dimulai dengan angka 0.',
            'nomor_telepon.min' => 'Nomor telepon terlalu pendek. Minimal :min angka.',
            'nomor_telepon.max' => 'Nomor telepon terlalu panjang. Maksimal :max angka.',
            'waktu_mulai.date_format' => 'Format waktu mulai tidak valid.',
            'waktu_selesai.date_format' => 'Format waktu selesai tidak valid.',
            'berulang_sampai.date_format' => 'Format tanggal berulang sampai tidak valid.',
            'tipe_berulang.in' => 'Tipe pengulangan harus salah satu dari: harian, mingguan.',
        ];
    }
}