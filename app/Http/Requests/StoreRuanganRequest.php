<?php

namespace App\Http\Requests;

use App\Models\Ruangan;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class StoreRuanganRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('ruangan_create');
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
        ];
    }
}