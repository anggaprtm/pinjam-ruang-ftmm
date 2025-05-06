<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StoreJadwalPerkuliahanRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('kuliah_create');
    }

    public function rules()
    {
        return [
            'ruangan_id' => ['required', 'integer', 'exists:ruangan,id'],
            'mata_kuliah' => ['required', 'string', 'max:255'],
            'hari' => ['required', 'in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu'],
            'tipe' => ['required', 'in:Kuliah Reguler,Seminar Proposal,Seminar Hasil,PHL'],
            'program_studi' => ['required', 'in:RN,TRKB,TI,TSD,TE'],
            'waktu_mulai' => [
                'required', 
                'date_format:H:i',
            ],
            'waktu_selesai' => [
                'required', 
                'date_format:H:i', 'after:waktu_mulai'
            ],
            'berlaku_mulai' => [
                'required', 
                'date_format:j M Y',
            ],
            'berlaku_sampai' => [
                'required', 
                'date_format:j M Y',
            ],
        ];
    }
}
