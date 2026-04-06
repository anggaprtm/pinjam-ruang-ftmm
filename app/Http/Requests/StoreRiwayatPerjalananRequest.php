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
            'user_id'       => ['nullable', 'exists:users,id'], 
            'tujuan'        => ['required', 'string', 'max:255'],
            'keperluan'     => ['nullable', 'string', 'max:255'],
            
            // UBAH BAGIAN INI
            'waktu_mulai'   => [
                'required',
                'date', // Cukup gunakan 'date' agar lebih fleksibel
            ],
            'waktu_selesai' => [
                'nullable',
                'date',
                'after:waktu_mulai', // Validasi ini tetap bisa jalan
            ],
        ];
    }
}