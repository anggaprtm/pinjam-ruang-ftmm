<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BotSetting;
use Illuminate\Http\Request;

class BotSettingController extends Controller
{
    public function index()
    {
        // Ambil setting pertama, atau buat instance baru kosong jika belum ada
        $setting = BotSetting::first() ?? new BotSetting();
        return view('admin.bot-setting.index', compact('setting'));
    }

    public function update(Request $request)
    {
        // Validasi simpel
        $request->validate([
            'pagi_jam' => 'required',
            'masuk_jam' => 'required',
            'pulang_jam' => 'required',
            'evaluasi_jam' => 'required',
        ]);

        // Pastikan checkbox yang tidak dicentang tetap tersimpan sebagai 0 (false)
        $data = $request->all();
        $data['pagi_aktif'] = $request->has('pagi_aktif');
        $data['masuk_aktif'] = $request->has('masuk_aktif');
        $data['pulang_aktif'] = $request->has('pulang_aktif');
        $data['evaluasi_aktif'] = $request->has('evaluasi_aktif');

        // Update ID 1 (Single Row Configuration)
        BotSetting::updateOrCreate(['id' => 1], $data);

        return back()->with('success', 'Pengaturan Bot berhasil diperbarui!');
    }
}