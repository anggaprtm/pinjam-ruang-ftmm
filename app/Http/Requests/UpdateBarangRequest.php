<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBarangRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('barang_edit');
    }

    public function rules()
    {
        return [
            'nama_barang' => [
                'string',
                'required',
                'max:255',
            ],
            'stok' => [
                'required',
                'integer',
                'min:0',
            ],
            'deskripsi' => [
                'nullable',
                'string',
            ],
            'foto' => [
                'nullable',
                'image',
                'max:2048',
            ],
        ];
    }
}
