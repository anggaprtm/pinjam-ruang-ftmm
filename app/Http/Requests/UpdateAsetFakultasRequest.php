<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAsetFakultasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('aset_fakultas')?->id ?? $this->route('aset_fakultas');

        return [
            'kode_barang' => ['required', 'string', 'max:100', \Illuminate\Validation\Rule::unique('aset_fakultas', 'kode_barang')->ignore($id)],
            'tahun_aset'  => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'nama_barang' => ['required', 'string', 'max:255'],
            'kondisi'     => ['required', 'in:Baik,Rusak Ringan,Rusak Berat'],
            'anggaran'    => ['required', 'in:DAMAS,HIBAH,IKU'], // <- Tambahkan ini
            'merk'        => ['nullable', 'string', 'max:100'],
            'deskripsi'   => ['nullable', 'string'],
            'ruangan_id'  => ['nullable', 'exists:ruangan,id'],
            'lokasi_text' => ['nullable', 'string', 'max:255'],
        ];
    }
}
