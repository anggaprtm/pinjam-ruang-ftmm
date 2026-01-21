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
    }

    public function rules()
    {
        return [
            'nama_kegiatan' => [
                'string',
                'required',
            ],
            'jenis_kegiatan' => ['required', 'in:Kegiatan Ormawa,Seminar Proposal,Sidang Skripsi,Rapat,Lomba,Lainnya'],
            'poster' => ['nullable', 'image', 'max:2048', 'mimes:jpeg,png,jpg,gif,svg'],
            'dosen_pembimbing_1' => ['nullable', 'string', 'max:255'],
            'dosen_pembimbing_2' => ['nullable', 'string', 'max:255'],
            'dosen_penguji_1'    => ['nullable', 'string', 'max:255'],
            'dosen_penguji_2'    => ['nullable', 'string', 'max:255'],
            'waktu_mulai' => [
                'required',
                'date_format:' . config('panel.date_format') . ' ' . config('panel.time_format'),
            ],
            'waktu_selesai' => [
                'required',
                'date_format:' . config('panel.date_format') . ' ' . config('panel.time_format'),
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