<?php

namespace App\Http\Requests;

use App\Models\Ruangan;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class UpdateRuanganRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('ruangan_edit');
    }

    public function rules()
    {
        return [
            'nama' => [
                'string',
                'required',
            ],
            'kapasitas' => [
                'nullable',
                'integer',
                'min:-2147483648',
                'max:2147483647',
            ],
            'lantai' => [
                'required',
                'integer',
                'min:1',
            ],
            'foto' => [
                'nullable', // Boleh kosong agar tidak wajib upload ulang
                'image',
                'max:2048',
            ],
        ];
    }
}