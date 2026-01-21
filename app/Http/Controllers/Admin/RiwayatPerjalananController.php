<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRiwayatPerjalananRequest;
use App\Http\Requests\UpdateRiwayatPerjalananRequest;
use App\Models\Mobil;
use App\Models\User;
use App\Models\RiwayatPerjalanan;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class RiwayatPerjalananController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('riwayat_perjalanan_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // bagian atas: ongoing / on duty
        $ongoing = RiwayatPerjalanan::with(['mobil', 'driver'])
            ->where('status', 'berlangsung')
            ->latest('waktu_mulai')
            ->get();

        if ($request->ajax()) {

            $query = RiwayatPerjalanan::with(['mobil', 'driver'])
                ->whereIn('status', ['terjadwal', 'selesai'])
                ->select(sprintf('%s.*', (new RiwayatPerjalanan())->table));

            $table = Datatables::of($query);

            // default order (kayak kegiatan)
            if (empty($request->input('order'))) {
                $table->order(function ($query) {
                    $query->orderBy('created_at', 'desc');
                });
            }

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            // ✅ Actions tanpa partial (ala RuanganController)
            $table->editColumn('actions', function ($row) {

                $status = strtolower($row->status ?? '');

                $btn = '<div class="text-center">';

                // ✅ kalau status selesai: hanya delete
                if ($status === 'selesai') {

                    if (Gate::allows('riwayat_perjalanan_delete')) {
                        $btn .= '
                            <form action="' . route('admin.riwayat-perjalanan.destroy', $row->id) . '"
                                method="POST"
                                class="d-inline js-delete-riwayat">
                                ' . csrf_field() . '
                                ' . method_field('DELETE') . '
                                <button type="submit" class="btn btn-xs btn-danger" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        ';
                    }

                    $btn .= '</div>';
                    return $btn;
                }

                // ✅ tombol MULAI hanya untuk booking/terjadwal
                if (
                    ($status === 'booking' || $status === 'terjadwal')
                    && Gate::allows('riwayat_perjalanan_edit')
                ) {
                    $btn .= '
                        <form action="' . route('admin.riwayat-perjalanan.mulai', $row->id) . '"
                            method="POST"
                            class="d-inline js-mulai-perjalanan">
                            ' . csrf_field() . '
                            ' . method_field('PATCH') . '
                            <button type="submit" class="btn btn-xs btn-success" title="Mulai">
                                <i class="fas fa-play"></i>
                            </button>
                        </form>';
                }

                // ✅ EDIT (selain selesai)
                if (Gate::allows('riwayat_perjalanan_edit')) {
                    $btn .= '
                        <a class="btn btn-xs btn-info"
                        href="' . route('admin.riwayat-perjalanan.edit', $row->id) . '"
                        title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                    ';
                }

                // ✅ DELETE
                if (Gate::allows('riwayat_perjalanan_delete')) {
                    $btn .= '
                        <form action="' . route('admin.riwayat-perjalanan.destroy', $row->id) . '"
                                method="POST"
                                class="d-inline js-delete-riwayat">
                                ' . csrf_field() . '
                                ' . method_field('DELETE') . '
                                <button type="submit" class="btn btn-xs btn-danger" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                    ';
                }

                $btn .= '</div>';

                return $btn;
            });

            // ========= kolom plain (supaya styling ikut modern-table css) =========
            $table->editColumn('id', function ($row) {
                return $row->id ?? '';
            });

            // ⚠️ kalau kamu pengen status pakai badge, boleh.
            // tapi kalau mau super clean, return plain text saja.
            $table->editColumn('status', function ($row) {
                $status = $row->status ?? '';

                // mapping status -> badge class
                $badgeClass = 'badge-status-selesai'; // default

                if ($status === 'terjadwal' || $status === 'booking') $badgeClass = 'badge-status-booking';
                if ($status === 'berlangsung' || $status === 'onduty') $badgeClass = 'badge-status-onduty';
                if ($status === 'selesai') $badgeClass = 'badge-status-selesai';

                // label tampilan
                $label = $status;
                if ($status === 'terjadwal') $label = 'Booking';
                if ($status === 'berlangsung') $label = 'On Duty';
                if ($status === 'selesai') $label = 'Selesai';

                return '<span class="badge-status ' . $badgeClass . '">' . e(ucfirst($label)) . '</span>';
            });

            $table->editColumn('waktu_mulai', function ($row) {

                $rawMulai = $row->getRawOriginal('waktu_mulai');
                if (!$rawMulai) return '-';

                $now = Carbon::now('Asia/Jakarta');

                $mulaiLabel   = $row->waktu_mulai;    // formatted dari accessor
                $selesaiLabel = $row->waktu_selesai;  // formatted dari accessor
                $status       = strtolower($row->status ?? '');

                /**
                 * ✅ 1) Kalau status sudah SELESAI → tampilkan waktu selesai saja
                 * (tidak peduli waktu mulai future/past)
                 */
                if ($status === 'selesai') {
                    // prefer tampil dari waktu_selesai kalau ada
                    if ($row->getRawOriginal('waktu_selesai')) {
                        return '
                            <div class="fw-semibold text-dark">' . e($mulaiLabel) . '</div>
                            <div class="small text-muted">Selesai: ' . e($selesaiLabel) . '</div>
                        ';
                    }

                    // fallback: kalau waktu_selesai kosong, pakai updated_at (waktu klik selesai)
                    $rawFinished = $row->getRawOriginal('updated_at');
                    $finishedAt = $rawFinished
                        ? Carbon::parse($rawFinished)->timezone('Asia/Jakarta')->format('d M Y H:i')
                        : '-';

                    return '
                        <div class="fw-semibold text-dark">' . e($mulaiLabel) . '</div>
                        <div class="small text-muted">Selesai: ' . e($finishedAt) . '</div>
                    ';
                }

                /**
                 * ✅ 2) Baru cek FUTURE (untuk yg belum selesai)
                 */
                $mulaiTime = Carbon::createFromFormat('Y-m-d H:i:s', $rawMulai, 'Asia/Jakarta');

                if ($mulaiTime->greaterThan($now)) {

                    $jarak = $now->diff($mulaiTime)->forHumans([
                        'parts' => 2,
                        'short' => false,
                        'syntax' => CarbonInterface::DIFF_ABSOLUTE,
                    ]);

                    return '
                        <div class="fw-semibold text-dark">' . e($mulaiLabel) . '</div>
                        <div class="small text-muted">' . e($jarak) . ' lagi</div>
                    ';
                }

                /**
                 * ✅ 3) Ongoing (optional)
                 */
                if ($status === 'berlangsung' || $status === 'onduty') {
                    return '
                        <div class="fw-semibold text-dark">' . e($mulaiLabel) . '</div>
                        <div class="small text-muted">🚗 sedang dijalan</div>
                    ';
                }

                /**
                 * ✅ 4) Default
                 */
                return '
                    <div class="fw-semibold text-dark">' . e($mulaiLabel) . '</div>
                    <div class="small text-muted">-</div>
                ';
            });




            $table->editColumn('tujuan', function ($row) {
                $tujuan = $row->tujuan ?? '-';
                $keperluan = $row->keperluan ?? '';

                return '
                    <div class="text-dark fw-bold">' . e($tujuan) . '</div>
                    <small class="text-muted">' . e($keperluan) . '</small>
                ';
            });


            // relasi mobil
            $table->addColumn('kendaraan', function ($row) {
                $nama = $row->mobil->nama_mobil ?? '-';
                $plat = $row->mobil->plat_nomor ?? '';

                return '
                    <div class="d-flex align-items-center">
                        <div class="me-2">
                            <span class="icon-circle icon-circle-sm">
                                <i class="fas fa-car"></i>
                            </span>
                        </div>
                        <div class="d-flex flex-column">
                            <span class="fw-bold text-dark">' . e($nama) . '</span>
                            ' . ($plat ? '<span class="plate-badge">' . e($plat) . '</span>' : '') . '
                        </div>
                    </div>
                ';
            });


            // relasi driver
            $table->addColumn('driver_display', function ($row) {
                $name = $row->driver->name ?? '-';

                // ambil inisial
                $initial = strtoupper(substr(trim($name), 0, 1));

                return '
                    <div class="d-flex align-items-center">
                        <div class="me-2">
                            <span class="user-initial">' . e($initial) . '</span>
                        </div>
                        <div class="fw-semibold text-dark">' . e($name) . '</div>
                    </div>
                ';
            });


            // ✅ penting: rawColumns
            $table->rawColumns(['actions', 'placeholder', 'status', 'kendaraan', 'driver_display', 'waktu_mulai', 'tujuan']);

            return $table->make(true);
        }

        return view('admin.riwayat_perjalanan.index', compact('ongoing'));
    }


    // ... (Method create, store, edit, update, destroy, dll TETAP SAMA seperti sebelumnya)
    // Pastikan method helper checkBentrok dll tetap ada di bawah sini
    public function create()
    {
        $mobils = Mobil::where('status', '!=', 'maintenance')->get();
        $users = [];
        if (auth()->user()->isAdmin()) {
            $users = User::whereHas('roles', function($q) { $q->where('title', 'Driver'); })->pluck('name', 'id')->prepend('-- Pilih Driver --', '');
        }
        return view('admin.riwayat_perjalanan.create', compact('mobils', 'users'));
    }

    public function store(StoreRiwayatPerjalananRequest $request)
    {
        $data = $request->validated();
        if (auth()->user()->isAdmin() && !empty($request->user_id)) { $data['user_id'] = $request->user_id; } else { $data['user_id'] = auth()->id(); }
        $format = config('panel.date_format') . ' ' . config('panel.time_format');
        $waktuMulai = Carbon::createFromFormat($format, $data['waktu_mulai']);
        $waktuSelesai = !empty($data['waktu_selesai']) ? Carbon::createFromFormat($format, $data['waktu_selesai']) : (clone $waktuMulai)->addHours(2);

        if ($this->checkBentrok($data['mobil_id'], $waktuMulai, $waktuSelesai)) {
            return back()->withInput()->withErrors(['mobil_id' => 'Mobil ini sudah dibooking/dipakai pada jam tersebut.']);
        }

        $now = Carbon::now();
        if ($waktuMulai->lte($now->copy()->addMinutes(15))) {
            $data['status'] = 'berlangsung';
            Mobil::where('id', $data['mobil_id'])->update(['status' => 'dipakai']);
        } else {
            $data['status'] = 'terjadwal';
        }
        RiwayatPerjalanan::create($data);
        return redirect()->route('admin.riwayat-perjalanan.index')->with('message', 'Jadwal berhasil disimpan.');
    }

    public function edit(RiwayatPerjalanan $riwayatPerjalanan)
    {
        $mobils = Mobil::all();
        $users = [];
        if (auth()->user()->isAdmin()) {
            $users = User::whereHas('roles', function($q) { $q->where('title', 'Driver'); })->pluck('name', 'id')->prepend('-- Pilih Driver --', '');
        }
        return view('admin.riwayat_perjalanan.edit', compact('riwayatPerjalanan', 'mobils', 'users'));
    }

    public function update(UpdateRiwayatPerjalananRequest $request, RiwayatPerjalanan $riwayatPerjalanan)
    {
        $data = $request->validated();
        if (auth()->user()->isAdmin() && !empty($request->user_id)) { $data['user_id'] = $request->user_id; }
        $format = config('panel.date_format') . ' ' . config('panel.time_format');
        $waktuMulai = Carbon::createFromFormat($format, $data['waktu_mulai']);
        $waktuSelesai = !empty($data['waktu_selesai']) ? Carbon::createFromFormat($format, $data['waktu_selesai']) : (clone $waktuMulai)->addHours(2);
        
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

    public function selesaikan(RiwayatPerjalanan $riwayatPerjalanan)
    {
        $riwayatPerjalanan->update(['status' => 'selesai', 'waktu_selesai' => Carbon::now()]);
        Mobil::where('id', $riwayatPerjalanan->mobil_id)->update(['status' => 'tersedia']);
        return back()->with('message', 'Tugas selesai.');
    }

    public function mulaiJalan(RiwayatPerjalanan $riwayatPerjalanan)
    {
        $mobil = Mobil::find($riwayatPerjalanan->mobil_id);
        if ($mobil->status == 'dipakai') { return back()->withErrors(['error' => 'Gagal memulai. Mobil fisik masih berstatus DIPAKAI.']); }
        $riwayatPerjalanan->update(['status' => 'berlangsung']);
        Mobil::where('id', $riwayatPerjalanan->mobil_id)->update(['status' => 'dipakai']);
        return back()->with('message', 'Hati-hati di jalan!');
    }

    private function checkBentrok($mobilId, $start, $end, $ignoreId = null)
    {
        return RiwayatPerjalanan::where('mobil_id', $mobilId)
            ->where('status', '!=', 'selesai')
            ->when($ignoreId, function($q) use ($ignoreId) { $q->where('id', '!=', $ignoreId); })
            ->where(function($q) use ($start, $end) {
                $q->where(function($sub) use ($start, $end) {
                    $sub->where('waktu_mulai', '<', $end)->where('waktu_selesai', '>', $start);
                });
            })->exists();
    }

    public function massDestroy(Request $request)
    {
        abort_if(Gate::denies('riwayat_perjalanan_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        RiwayatPerjalanan::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}