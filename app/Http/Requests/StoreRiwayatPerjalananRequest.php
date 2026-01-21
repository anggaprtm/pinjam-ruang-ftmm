<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StoreRiwayatPerjalananRequest extends FormRequest
{
    public function authorize()
    {
        // Sesuaikan permission gate kamu
        return true; 
    }

    public function rules()
    {
        return [
            'mobil_id'      => ['required', 'exists:mobils,id'],
            'user_id'       => ['nullable', 'exists:users,id'], // Tambahan untuk Admin
            'tujuan'        => ['required', 'string', 'max:255'],
            'keperluan'     => ['nullable', 'string', 'max:255'],
            'waktu_mulai'   => [
                'required',
                'date_format:' . config('panel.date_format') . ' ' . config('panel.time_format'),
            ],
            'waktu_selesai' => [
                'nullable',
                'date_format:' . config('panel.date_format') . ' ' . config('panel.time_format'),
                'after:waktu_mulai',
            ],
        ];
    }
}