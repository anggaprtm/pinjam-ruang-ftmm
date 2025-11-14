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
                'required', // atau 'required' jika wajib
            ],
            'nomor_telepon' => [
                'required',
                // Hanya angka dan harus diawali dengan 0, misal: 08123456789
                'regex:/^0[0-9]+$/',
                'min:9', // asumsi: minimal 9 digit
                'max:15', // asumsi: maksimal 15 digit
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

    /**
     * Customize error messages for validation.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'nomor_telepon.required' => 'Field nomor telepon wajib diisi.',
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