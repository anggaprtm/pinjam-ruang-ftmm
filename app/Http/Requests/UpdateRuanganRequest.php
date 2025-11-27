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
                'max:10240',
            ],
        ];
    }
    
    public function messages()
    {
        return [
            'foto.uploaded' => 'Foto gagal diunggah. Ukuran file mungkin terlalu besar atau terjadi kesalahan saat upload.',
            'foto.image'    => 'File yang diunggah harus berupa gambar (jpg, jpeg, png).',
            'foto.max'      => 'Ukuran foto maksimal 10MB.',
        ];
    }
}