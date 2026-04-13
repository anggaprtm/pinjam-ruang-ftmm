<?php

/**
 * ============================================================
 * PATCH untuk RiwayatPerjalananController.php
 * Tambahkan / ganti method-method berikut
 * ============================================================
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRiwayatPerjalananRequest;
use App\Http\Requests\UpdateRiwayatPerjalananRequest;
use App\Models\Mobil;
use App\Models\User;
use App\Models\RiwayatPerjalanan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Gate;
use Symfony\Component\HttpFoundation\Response;
use App\Models\RiwayatBbm;
use Illuminate\Support\Facades\DB;

class RiwayatPerjalananController extends Controller
{
    // ============================================================
    // INDEX — tambah $kmHariIni dan $kmKemarinAwal ke view
    // ============================================================
    public function index(Request $request)
    {
        abort_if(Gate::denies('riwayat_perjalanan_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $ongoing = RiwayatPerjalanan::with(['mobil', 'driver'])
            ->where('status', 'berlangsung')
            ->latest('waktu_mulai')
            ->get();

        if ($request->ajax()) {

            $query = RiwayatPerjalanan::with(['mobil', 'driver'])
                ->whereIn('status', ['terjadwal', 'selesai'])
                ->select(sprintf('%s.*', (new RiwayatPerjalanan())->table));

            $table = Datatables::of($query);

            if (empty($request->input('order'))) {
                $table->order(fn($q) => $q->orderBy('created_at', 'desc'));
            }

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {
                $status = strtolower($row->status ?? '');
                $btn = '<div class="text-center">';

                if ($status === 'selesai') {
                    if (Gate::allows('riwayat_perjalanan_delete')) {
                        $btn .= '<form action="' . route('admin.riwayat-perjalanan.destroy', $row->id) . '" method="POST" class="d-inline js-delete-riwayat">'
                            . csrf_field() . method_field('DELETE')
                            . '<button type="submit" class="btn btn-xs btn-danger" title="Hapus"><i class="fas fa-trash"></i></button></form>';
                    }
                    $btn .= '</div>';
                    return $btn;
                }

                if (in_array($status, ['booking', 'terjadwal']) && Gate::allows('riwayat_perjalanan_edit')) {
                    $btn .= '<form action="' . route('admin.riwayat-perjalanan.mulai', $row->id) . '" method="POST" class="d-inline js-mulai-perjalanan">'
                        . csrf_field() . method_field('PATCH')
                        . '<button type="submit" class="btn btn-xs btn-success" title="Mulai"><i class="fas fa-play"></i></button></form>';
                }

                if (Gate::allows('riwayat_perjalanan_edit')) {
                    $btn .= '<a class="btn btn-xs btn-info" href="' . route('admin.riwayat-perjalanan.edit', $row->id) . '" title="Edit"><i class="fas fa-edit"></i></a>';
                }

                if (Gate::allows('riwayat_perjalanan_delete')) {
                    $btn .= '<form action="' . route('admin.riwayat-perjalanan.destroy', $row->id) . '" method="POST" class="d-inline js-delete-riwayat">'
                        . csrf_field() . method_field('DELETE')
                        . '<button type="submit" class="btn btn-xs btn-danger" title="Hapus"><i class="fas fa-trash"></i></button></form>';
                }

                $btn .= '</div>';
                return $btn;
            });

            $table->editColumn('status', function ($row) {
                $status = $row->status ?? '';
                $badgeClass = 'badge-status-selesai';
                if (in_array($status, ['terjadwal', 'booking'])) $badgeClass = 'badge-status-booking';
                if (in_array($status, ['berlangsung', 'onduty'])) $badgeClass = 'badge-status-onduty';
                $label = match($status) {
                    'terjadwal'   => 'Booking',
                    'berlangsung' => 'On Duty',
                    'selesai'     => 'Selesai',
                    default       => ucfirst($status),
                };
                return '<span class="badge-status ' . $badgeClass . '">' . e($label) . '</span>';
            });

            $table->editColumn('waktu_mulai', function ($row) {
                $rawMulai = $row->getRawOriginal('waktu_mulai');
                if (!$rawMulai) return '-';

                $now          = Carbon::now('Asia/Jakarta');
                $mulaiLabel   = $row->waktu_mulai;
                $selesaiLabel = $row->waktu_selesai;
                $status       = strtolower($row->status ?? '');

                if ($status === 'selesai') {
                    $finishedAt = $row->getRawOriginal('waktu_selesai')
                        ? e($selesaiLabel)
                        : Carbon::parse($row->getRawOriginal('updated_at'))->timezone('Asia/Jakarta')->format('d M Y H:i');

                    return '<div class="fw-semibold text-dark">' . e($mulaiLabel) . '</div>'
                        . '<div class="small text-muted">Selesai: ' . $finishedAt . '</div>';
                }

                $mulaiTime = Carbon::createFromFormat('Y-m-d H:i:s', $rawMulai, 'Asia/Jakarta');

                if ($mulaiTime->greaterThan($now)) {
                    $diff = $now->diffForHumans($mulaiTime, ['parts' => 1, 'syntax' => CarbonInterface::DIFF_ABSOLUTE]);
                    return '<div class="fw-semibold text-dark">' . e($mulaiLabel) . '</div>'
                        . '<div class="small text-muted">' . e($diff) . ' lagi</div>';
                }

                if (in_array($status, ['berlangsung', 'onduty'])) {
                    return '<div class="fw-semibold text-dark">' . e($mulaiLabel) . '</div>'
                        . '<div class="small text-muted">🚗 sedang dijalan</div>';
                }

                return '<div class="fw-semibold text-dark">' . e($mulaiLabel) . '</div><div class="small text-muted">-</div>';
            });

            $table->editColumn('tujuan', function ($row) {
                return '<div class="text-dark fw-bold">' . e($row->tujuan ?? '-') . '</div>'
                    . '<small class="text-muted">' . e($row->keperluan ?? '') . '</small>';
            });

            $table->addColumn('kendaraan', function ($row) {
                $nama = $row->mobil->nama_mobil ?? '-';
                $plat = $row->mobil->plat_nomor ?? '';
                return '<div class="d-flex align-items-center">'
                    . '<span class="icon-circle icon-circle-sm me-2"><i class="fas fa-car"></i></span>'
                    . '<div><span class="fw-bold text-dark">' . e($nama) . '</span>'
                    . ($plat ? '<br><span class="plate-badge">' . e($plat) . '</span>' : '')
                    . '</div></div>';
            });

            $table->addColumn('driver_display', function ($row) {
                $name    = $row->driver->name ?? '-';
                $initial = strtoupper(substr(trim($name), 0, 1));
                return '<div class="d-flex align-items-center">'
                    . '<span class="user-initial me-2">' . e($initial) . '</span>'
                    . '<span class="fw-semibold text-dark">' . e($name) . '</span></div>';
            });

            // ✅ KOLOM BARU: km_info
            $table->addColumn('km_info', function ($row) {
                $kmAwal  = $row->km_awal;
                $kmAkhir = $row->km_akhir;

                if (!$kmAwal) return '<span class="text-muted small">—</span>';

                $html = '<span class="km-badge"><i class="fas fa-tachometer-alt me-1"></i>' . number_format($kmAwal) . '</span>';

                if ($kmAkhir) {
                    $selisih = $kmAkhir - $kmAwal;
                    $html .= '<br><span class="km-selisih-badge mt-1"><i class="fas fa-route me-1"></i>+' . number_format($selisih) . ' km</span>';
                }

                return $html;
            });

            $table->rawColumns(['actions', 'placeholder', 'status', 'kendaraan', 'driver_display', 'waktu_mulai', 'tujuan', 'km_info']);

            return $table->make(true);
        }

        // ✅ Ambil data KM hari ini untuk summary card
        $today = Carbon::today('Asia/Jakarta');

        // 🔥 LOGIC MASTER ODOMETER 🔥
        // Ambil KM dari Awal Jalan, Akhir Jalan, dan Isi Bensin, lalu urutkan dari yang terbaru
        $kmAwal = DB::table('riwayat_perjalanans')
            ->whereNull('deleted_at')->whereNotNull('km_awal')
            ->select('waktu_mulai as waktu', 'km_awal as km', DB::raw("'Trip Berangkat' as sumber"));

        $kmAkhir = DB::table('riwayat_perjalanans')
            ->whereNull('deleted_at')->whereNotNull('km_akhir')
            ->select('waktu_selesai as waktu', 'km_akhir as km', DB::raw("'Trip Selesai' as sumber"));

        $kmBensin = DB::table('riwayat_bbms')
            ->whereNotNull('km_odometer')
            ->select('tanggal as waktu', 'km_odometer as km', DB::raw("'Isi BBM' as sumber"));

        // Gabungkan ketiganya, urutkan waktu terbaru, ambil 2 teratas
        $allKm = $kmAwal->union($kmAkhir)->union($kmBensin)
            ->orderBy('waktu', 'desc')
            ->limit(2)
            ->get();

        $kmTerakhir = $allKm->first(); // Data Paling Baru
        $kmSebelumnya = $allKm->count() > 1 ? $allKm->last() : null; // Data Tepat Sebelumnya
        
        // Data untuk tabel BBM
        $riwayatBbm = RiwayatBbm::orderBy('tanggal', 'desc')->get();

        return view('admin.riwayat_perjalanan.index', compact('ongoing', 'kmTerakhir', 'kmSebelumnya', 'riwayatBbm'));
    }

    // SIMPAN DATA BBM
    public function storeBbm(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'km_odometer' => 'required|integer',
            'biaya' => 'nullable|integer'
        ]);

        RiwayatBbm::create($request->all());
        return back()->with('success', 'Data pengisian bensin dan KM berhasil dicatat.');
    }

    // HAPUS DATA BBM
    public function destroyBbm($id)
    {
        RiwayatBbm::findOrFail($id)->delete();
        return back()->with('success', 'Data bensin berhasil dihapus.');
    }


    // ============================================================
    // CREATE — tambah cek $sudahAdaKmHariIni
    // ============================================================
    public function create()
    {
        $mobils = Mobil::where('status', '!=', 'maintenance')->get();
        $users  = [];
        if (auth()->user()->isAdmin()) {
            $users = User::whereHas('roles', fn($q) => $q->where('title', 'Driver'))
                ->pluck('name', 'id')
                ->prepend('-- Pilih Driver --', '');
        }

        // Cek apakah sudah ada trip hari ini yang punya km_awal
        $today = Carbon::today('Asia/Jakarta');

        $sudahAdaKmHariIni = RiwayatPerjalanan::whereDate('waktu_mulai', $today)
            ->whereNotNull('km_awal')
            ->exists();

        $kmHariIni = $sudahAdaKmHariIni
            ? RiwayatPerjalanan::whereDate('waktu_mulai', $today)->whereNotNull('km_awal')->orderBy('waktu_mulai')->value('km_awal')
            : null;

        return view('admin.riwayat_perjalanan.create', compact('mobils', 'users', 'sudahAdaKmHariIni', 'kmHariIni'));
    }


    // ============================================================
    // STORE — tangkap km_awal
    // ============================================================
    public function store(StoreRiwayatPerjalananRequest $request)
    {
        $data = $request->validated();

        if (auth()->user()->isAdmin() && !empty($request->user_id)) {
            $data['user_id'] = $request->user_id;
        } else {
            $data['user_id'] = auth()->id();
        }

        $waktuMulai  = Carbon::parse($data['waktu_mulai']);
        $waktuSelesai = !empty($data['waktu_selesai'])
            ? Carbon::parse($data['waktu_selesai'])
            : (clone $waktuMulai)->addHours(2);

        if ($this->checkBentrok($data['mobil_id'], $waktuMulai, $waktuSelesai)) {
            return back()->withInput()->withErrors(['mobil_id' => 'Mobil ini sudah dibooking/dipakai pada jam tersebut.']);
        }

        $now = Carbon::now();
        $data['status'] = $waktuMulai->lte($now->copy()->addMinutes(15)) ? 'berlangsung' : 'terjadwal';

        if ($data['status'] === 'berlangsung') {
            Mobil::where('id', $data['mobil_id'])->update(['status' => 'dipakai']);
        }

        // ✅ km_awal: hanya simpan jika belum ada km hari ini
        if (!empty($request->km_awal)) {
            $today = Carbon::today('Asia/Jakarta');
            $sudahAda = RiwayatPerjalanan::whereDate('waktu_mulai', $today)->whereNotNull('km_awal')->exists();
            if (!$sudahAda) {
                $data['km_awal'] = (int) $request->km_awal;
            }
        }

        // hapus km_awal dari $data kalau tidak ada (supaya tidak error validated)
        if (empty($data['km_awal'])) unset($data['km_awal']);

        RiwayatPerjalanan::create($data);

        return redirect()->route('admin.riwayat-perjalanan.index')->with('message', 'Jadwal berhasil disimpan.');
    }


    // ============================================================
    // SELESAIKAN — tambah input km_akhir (opsional via PATCH)
    // ============================================================
    public function selesaikan(Request $request, RiwayatPerjalanan $riwayatPerjalanan)
    {
        $updateData = [
            'status'       => 'selesai',
            'waktu_selesai' => Carbon::now(),
        ];

        // Kalau driver input km_akhir saat selesaikan
        if ($request->filled('km_akhir')) {
            $updateData['km_akhir'] = (int) $request->km_akhir;
        }

        $riwayatPerjalanan->update($updateData);
        Mobil::where('id', $riwayatPerjalanan->mobil_id)->update(['status' => 'tersedia']);

        return back()->with('message', 'Tugas selesai.');
    }


    // ============================================================
    // EDIT, UPDATE, DESTROY, mulaiJalan, checkBentrok, massDestroy
    // — TIDAK BERUBAH dari versi original —
    // ============================================================
    public function edit(RiwayatPerjalanan $riwayatPerjalanan)
    {
        $mobils = Mobil::all();
        $users  = [];
        if (auth()->user()->isAdmin()) {
            $users = User::whereHas('roles', fn($q) => $q->where('title', 'Driver'))
                ->pluck('name', 'id')
                ->prepend('-- Pilih Driver --', '');
        }
        return view('admin.riwayat_perjalanan.edit', compact('riwayatPerjalanan', 'mobils', 'users'));
    }

    public function update(UpdateRiwayatPerjalananRequest $request, RiwayatPerjalanan $riwayatPerjalanan)
    {
        $data = $request->validated();

        if (auth()->user()->isAdmin() && !empty($request->user_id)) {
            $data['user_id'] = $request->user_id;
        }

        $waktuMulai  = Carbon::parse($data['waktu_mulai']);
        $waktuSelesai = !empty($data['waktu_selesai'])
            ? Carbon::parse($data['waktu_selesai'])
            : (clone $waktuMulai)->addHours(2);

        if ($this->checkBentrok($data['mobil_id'], $waktuMulai, $waktuSelesai, $riwayatPerjalanan->id)) {
            return back()->withInput()->withErrors(['mobil_id' => 'Mobil ini sudah dibooking/dipakai pada jam tersebut.']);
        }

        $riwayatPerjalanan->update($data);
        return redirect()->route('admin.riwayat-perjalanan.index')->with('message', 'Data berhasil diperbarui.');
    }

    public function destroy(RiwayatPerjalanan $riwayatPerjalanan)
    {
        if ($riwayatPerjalanan->status == 'berlangsung') {
            Mobil::where('id', $riwayatPerjalanan->mobil_id)->update(['status' => 'tersedia']);
        }
        $riwayatPerjalanan->delete();
        return back()->with('message', 'Data berhasil dihapus.');
    }

    public function mulaiJalan(RiwayatPerjalanan $riwayatPerjalanan)
    {
        $mobil = Mobil::find($riwayatPerjalanan->mobil_id);
        if ($mobil->status == 'dipakai') {
            return back()->withErrors(['error' => 'Gagal memulai. Mobil fisik masih berstatus DIPAKAI.']);
        }
        $riwayatPerjalanan->update(['status' => 'berlangsung']);
        Mobil::where('id', $riwayatPerjalanan->mobil_id)->update(['status' => 'dipakai']);
        return back()->with('message', 'Hati-hati di jalan!');
    }

    private function checkBentrok($mobilId, $start, $end, $ignoreId = null)
    {
        return RiwayatPerjalanan::where('mobil_id', $mobilId)
            ->where('status', '!=', 'selesai')
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->where(function ($q) use ($start, $end) {
                $q->where(fn($sub) => $sub->where('waktu_mulai', '<', $end)->where('waktu_selesai', '>', $start));
            })->exists();
    }

    public function massDestroy(Request $request)
    {
        abort_if(Gate::denies('riwayat_perjalanan_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        RiwayatPerjalanan::whereIn('id', request('ids'))->delete();
        return response(null, Response::HTTP_NO_CONTENT);
    }
}