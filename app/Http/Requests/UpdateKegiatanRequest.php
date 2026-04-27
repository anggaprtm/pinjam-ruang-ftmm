<?php

namespace App\Http\Requests;

use App\Models\Kegiatan;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class UpdateKegiatanRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('kegiatan_edit');
    }

    protected function prepareForValidation()
    {
        // Jika user_id tidak dikirimkan (mis. user biasa), set ke user yang sedang login
        if (! $this->has('user_id')) {
            $this->merge([
                'user_id' => auth()->id(),
            ]);
        }

        // Trik Ninja: Translate bulan ID -> EN untuk Tempus Dominus
        $translateDate = function($dateStr) {
            if (!$dateStr) return $dateStr;
            $id = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            $en = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            return str_replace($id, $en, $dateStr);
        };

        $this->merge([
            'waktu_mulai'   => $this->has('waktu_mulai') ? $translateDate($this->waktu_mulai) : null,
            'waktu_selesai' => $this->has('waktu_selesai') ? $translateDate($this->waktu_selesai) : null,
        ]);
    }

    public function rules()
    {
        return [
            'nama_kegiatan' => [
                'string',
                'required',
            ],
            'jenis_kegiatan' => ['required', 'in:Kegiatan Ormawa,Seminar Proposal,Sidang Skripsi,Rapat,Lomba,PHL, Kuliah Tamu, UTS, UAS, Lainnya'],
            'poster' => ['nullable', 'image', 'max:2048', 'mimes:jpeg,png,jpg,gif,svg'],
            'dosen_pembimbing_1' => ['nullable', 'string', 'max:255'],
            'dosen_pembimbing_2' => ['nullable', 'string', 'max:255'],
            'dosen_penguji_1'    => ['nullable', 'string', 'max:255'],
            'dosen_penguji_2'    => ['nullable', 'string', 'max:255'],
            'pengawas' => ['nullable', 'string', 'max:255'],
            'waktu_mulai' => [
                'required',
                'date',
            ],
            'waktu_selesai' => [
                'required',
                'date',
            ],
            'user_id' => [
                'required',
                'integer',
            ],
            // 'nomor_telepon' => [
            //     'string',
            //     'nullable',
            // ],
            'nama_pic' => [
                'string',
                'nullable', // atau 'required' jika wajib
            ],
            'surat_izin' => [
                'nullable',
                'file',
                'mimes:pdf',
                'max:2048'
            ],
        ];
    }
}