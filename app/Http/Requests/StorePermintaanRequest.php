<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePermintaanRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Sesuaikan dengan permission gate nanti
    }

    public function rules()
    {
        return [
            'pic_user_id' => 'required|exists:users,id',
            'nama_kegiatan' => 'required|string|max:255',
            'jenis_kegiatan' => 'required|string',
            'tanggal_kegiatan' => 'required|date',
            'waktu_mulai' => 'required',
            'waktu_selesai' => 'required',
            'jumlah_peserta' => 'nullable|integer',
            'lampiran' => 'nullable|file|mimes:pdf,jpg,png|max:5120', // Max 5MB
            
            // Validasi Konsumsi (Jika dicentang)
            'waktu_konsumsi' => 'required_if:request_konsumsi,1',
            'catatan_konsumsi' => 'nullable|string',
        ];
    }
}