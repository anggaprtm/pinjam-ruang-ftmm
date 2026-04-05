<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgendaFakultas;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgendaFakultasController extends Controller
{
    // ── CRUD Admin ───────────────────────────────────────────────

    public function index()
    {
        $agendas = AgendaFakultas::orderBy('tanggal_mulai')->paginate(20);
        return view('admin.agenda-fakultas.index', compact('agendas'));
    }

    public function create()
    {
        $kategoriOptions = ['Akademik', 'Wisuda', 'Kemahasiswaan', 'Penelitian', 'Pengabdian', 'Lainnya'];
        $warnaOptions    = [
            '#2dd4bf' => 'Teal (default)',
            '#3b82f6' => 'Biru',
            '#f59e0b' => 'Amber',
            '#ef4444' => 'Merah',
            '#8b5cf6' => 'Ungu',
            '#10b981' => 'Hijau',
        ];
        return view('admin.agenda-fakultas.create', compact('kategoriOptions', 'warnaOptions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul'             => 'required|string|max:255',
            'deskripsi'         => 'nullable|string',
            'kategori'          => 'required|string',
            'warna'             => 'required|string',
            'tanggal_mulai'     => 'required|date',
            'tanggal_selesai'   => 'nullable|date|after_or_equal:tanggal_mulai',
            'waktu_mulai'       => 'nullable|date_format:H:i',
            'waktu_selesai'     => 'nullable|date_format:H:i',
            'is_all_day'        => 'boolean',
            'tampil_di_signage' => 'boolean',
            'tampil_countdown'  => 'boolean',
            'urutan'            => 'integer|min:0',
        ]);

        $validated['created_by']  = Auth::id();
        $validated['is_all_day']  = $request->boolean('is_all_day', true);
        $validated['tampil_di_signage'] = $request->boolean('tampil_di_signage', true);
        $validated['tampil_countdown']  = $request->boolean('tampil_countdown', false);

        AgendaFakultas::create($validated);

        return redirect()->route('admin.agenda-fakultas.index')
            ->with('success', 'Agenda berhasil ditambahkan.');
    }

    public function edit(AgendaFakultas $agendaFakultas)
    {
        $kategoriOptions = ['Akademik', 'Wisuda', 'Kemahasiswaan', 'Penelitian', 'Pengabdian', 'Lainnya'];
        $warnaOptions    = [
            '#2dd4bf' => 'Teal (default)',
            '#3b82f6' => 'Biru',
            '#f59e0b' => 'Amber',
            '#ef4444' => 'Merah',
            '#8b5cf6' => 'Ungu',
            '#10b981' => 'Hijau',
        ];
        return view('admin.agenda-fakultas.edit', compact('agendaFakultas', 'kategoriOptions', 'warnaOptions'));
    }

    public function update(Request $request, AgendaFakultas $agendaFakultas)
    {
        $validated = $request->validate([
            'judul'             => 'required|string|max:255',
            'deskripsi'         => 'nullable|string',
            'kategori'          => 'required|string',
            'warna'             => 'required|string',
            'tanggal_mulai'     => 'required|date',
            'tanggal_selesai'   => 'nullable|date|after_or_equal:tanggal_mulai',
            'waktu_mulai'       => 'nullable|date_format:H:i',
            'waktu_selesai'     => 'nullable|date_format:H:i',
            'is_all_day'        => 'boolean',
            'tampil_di_signage' => 'boolean',
            'tampil_countdown'  => 'boolean',
            'urutan'            => 'integer|min:0',
        ]);

        $validated['is_all_day']        = $request->boolean('is_all_day', true);
        $validated['tampil_di_signage'] = $request->boolean('tampil_di_signage', true);
        $validated['tampil_countdown']  = $request->boolean('tampil_countdown', false);

        $agendaFakultas->update($validated);

        return redirect()->route('admin.agenda-fakultas.index')
            ->with('success', 'Agenda berhasil diperbarui.');
    }

    public function destroy(AgendaFakultas $agendaFakultas)
    {
        $agendaFakultas->delete();
        return redirect()->route('admin.agenda-fakultas.index')
            ->with('success', 'Agenda berhasil dihapus.');
    }

    // ── API untuk Signage ────────────────────────────────────────

    /**
     * GET /api/v1/signage/agenda-fakultas
     * Dipakai oleh frontend React signage
     */
    public function apiIndex()
    {
        Carbon::setLocale('id');
        $today = Carbon::today();

        // Agenda mendatang untuk panel kalender
        $agendas = AgendaFakultas::untukSignage()
            ->limit(10)
            ->get()
            ->map(fn($a) => [
                'id'              => $a->id,
                'judul'           => $a->judul,
                'deskripsi'       => $a->deskripsi,
                'kategori'        => $a->kategori,
                'warna'           => $a->warna,
                'tanggal_mulai'   => $a->tanggal_mulai->format('Y-m-d'),
                'tanggal_selesai' => $a->tanggal_selesai?->format('Y-m-d'),
                'waktu_mulai'     => $a->waktu_mulai,
                'waktu_selesai'   => $a->waktu_selesai,
                'is_all_day'      => $a->is_all_day,
                'is_ongoing'      => $a->is_ongoing,
                'sisa_hari'       => $a->sisa_hari,
                'sisa_waktu_label'=> $a->sisa_waktu_label,
                // Format display
                'date_day'        => $a->tanggal_mulai->format('d'),
                'date_month'      => $a->tanggal_mulai->translatedFormat('M'),
                'date_full'       => $a->tanggal_mulai->translatedFormat('l, d F Y'),
            ]);

        // Countdown items (hanya yang flag tampil_countdown = true)
        $countdowns = AgendaFakultas::countdown()
            ->limit(3)
            ->get()
            ->map(fn($a) => [
                'id'               => $a->id,
                'judul'            => $a->judul,
                'kategori'         => $a->kategori,
                'warna'            => $a->warna,
                'sisa_hari'        => $a->sisa_hari,
                'sisa_waktu_label' => $a->sisa_waktu_label,
                'date_full'        => $a->tanggal_mulai->translatedFormat('d F Y'),
                'is_ongoing'       => $a->is_ongoing,
            ]);

        return response()->json([
            'agendas'    => $agendas,
            'countdowns' => $countdowns,
        ]);
    }
}