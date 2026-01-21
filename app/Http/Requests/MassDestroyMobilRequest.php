<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Response;

class MassDestroyMobilRequest extends FormRequest
{
    public function authorize()
    {
        // Biasanya pakai permission delete
        return Gate::allows('mobil_delete');
    }

    public function rules()
    {
        return [
            'ids'   => 'required|array',
            'ids.*' => 'exists:mobils,id',
        ];
    }
}