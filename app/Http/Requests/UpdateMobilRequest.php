<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Response;

class UpdateMobilRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('mobil_edit');
    }

    public function rules()
    {
        return [
            'nama_mobil' => [
                'string',
                'required',
            ],
            'plat_nomor' => [
                'string',
                'required',
                'unique:mobils,plat_nomor,' . request()->route('mobil')->id, // Ignore current ID
            ],
            'warna' => [
                'string',
                'nullable',
            ],
            'status' => [
                'required',
                'in:tersedia,dipakai,maintenance',
            ],
        ];
    }
}