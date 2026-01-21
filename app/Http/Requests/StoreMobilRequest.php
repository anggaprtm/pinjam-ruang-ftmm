<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Response;

class StoreMobilRequest extends FormRequest
{
    public function authorize()
    {
        // Pastikan permission di database sudah ada, atau ganti true dulu untuk dev
        return Gate::allows('mobil_create'); 
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
                'unique:mobils', // Gak boleh ada plat nomor kembar
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