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

    public function rules()
    {
        return [
            'nama_kegiatan' => [
                'string',
                'required',
            ],
            'nama_pic' => [
                'string',
                'nullable', // atau 'required' jika wajib
            ],
            'nomor_telepon' => [
                'string',
                'required',
            ],
            'waktu_mulai' => [
                'required',
                'date_format:' . config('panel.date_format') . ' ' . config('panel.time_format'),
            ],
            'waktu_selesai' => [
                'required',
                'date_format:' . config('panel.date_format') . ' ' . config('panel.time_format'),
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
                'nullable', // Membolehkan field ini kosong
                'date_format:' . config('panel.date_format'), // Pastikan formatnya benar
        ],

        'tipe_berulang' => [
            'required_with:berulang_sampai', // Wajib diisi jika 'berulang_sampai' ada isinya
            'string',
            'in:harian,mingguan', // Pastikan nilainya hanya salah satu dari dua ini
        ],
        ];
    }
}