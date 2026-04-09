<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MassDestroyAsetFakultasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ids'   => ['required', 'array'],
            'ids.*' => ['exists:aset_fakultas,id'],
        ];
    }
}
