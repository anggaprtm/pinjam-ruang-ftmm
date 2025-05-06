<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateJadwalPerkuliahanRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('kuliah_edit');
    }

    public function rules()
    {
        return [
            'ruangan_id' => ['required', 'integer', 'exists:ruangan,id'],
            'mata_kuliah' => ['required', 'string', 'max:255'],
            'dosen' => ['required', 'string', 'max:255'],
            'hari' => ['required', 'in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu'],
            'waktu_mulai' => ['required', 'date_format:H:i'],
            'waktu_selesai' => ['required', 'date_format:H:i', 'after:waktu_mulai'],
        ];
    }
}
