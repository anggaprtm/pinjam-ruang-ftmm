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
        ];
    }
}