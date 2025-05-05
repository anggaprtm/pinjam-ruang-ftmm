<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ruangan;
use App\Models\User;
use Gate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SystemCalendarController extends Controller
{
    public $sources = [
        [
            'model'      => '\App\Models\Kegiatan',
            'date_field' => 'waktu_mulai',
            'end_field'  => 'waktu_selesai',
            'field'      => 'nama_kegiatan',
            'prefix'     => '',
            'suffix'     => '',
            'route'      => 'admin.kegiatan.edit',
        ],
    ];

    public function index(Request $request)
    {
        abort_if(Gate::denies('calendar_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $events = [];
        $ruangan = Ruangan::all()->pluck('nama', 'id')->prepend(trans('Semua Ruang'), '');
        $users = User::all()->pluck('name', 'id')->prepend(trans('Semua Peminjam'), '');
        $userColors = [];

        // Ambil nilai filter dari request
        $filterRuangan = $request->input('ruangan_id');
        $filterPeminjam = $request->input('user_id');
        $filterKuliah = $request->input('filter_kuliah', 'non-kuliah'); // Nilai filter baru untuk jenis kegiatan

        // Loop melalui setiap sumber data dan terapkan filter
        foreach ($this->sources as $source) {
            $query = $source['model']::query();

            $query->where('status', 'disetujui');

            // Jika filter ruangan diterapkan
            if ($filterRuangan) {
                $query->where('ruangan_id', $filterRuangan);
            }

            // Jika filter peminjam diterapkan
            if ($filterPeminjam) {
                $query->where('user_id', $filterPeminjam);
            }

            // Filter untuk kegiatan perkuliahan atau selain perkuliahan
            if ($filterKuliah == 'kuliah') {
                $query->where('deskripsi', 'Kuliah');
            } elseif ($filterKuliah == 'non-kuliah') {
                $query->where(function ($query) {
                    $query->where('deskripsi', '!=', 'Kuliah')
                          ->orWhereNull('deskripsi')
                          ->orWhere('deskripsi', '');
                });
            }

            // Mendapatkan data yang sesuai dengan filter
            foreach ($query->get() as $model) {
                $startDate = $model->getAttributes()[$source['date_field']];
                $endDate = $model->getAttributes()[$source['end_field']];
                if (!$startDate || !$endDate) {
                    continue;
                }

                $color = $this->getUserColor($model->user_id);
                $events[] = [
                    'title' => trim($source['prefix'] . " " . $model->{$source['field']} . " " . $source['suffix']. " | (" . $model->ruangan->nama . ")"),
                    'start' => $startDate,
                    'end' => $endDate,
                    'url' => route($source['route'], $model->id),
                    'color' => $color,  
                ];

                if ($model->user && !isset($userColors[$model->user->id])) {
                    $userColors[$model->user->name] = $this->getUserColor($model->user->id);
                }
            }
        }

        return view('admin.calendar.calendar', compact('events', 'ruangan', 'users', 'userColors'));
    }

    private function getUserColor($userId)
    {
        $colors = [
            1 => '#1E90FF', // Admin
            2 => '#9e0142', // HIMATESDA
            3 => '#d53e4f', // HIMATERA
            4 => '#f46d43', // HIMANO
            5 => '#fdae61', // HMTI
            6 => '#fee08b', // IME
            7 => '#e6f598', // BEM FTMM
            8 => '#abdda4', // BLM FTMM
            9 => '#66c2a5', // ATOM
            10 => '#3288bd', // PD FTMM
            11 => '#5e4fa2', // KOMBO UA
            12 => '#a4c2f4', // TI
            13 => '#b2b200', // RN
            14 => '#00ff00', // TE
            15 => '#d5a6bd', // TSD
            16 => '#ea9999', // TRKB
        ];

        return $colors[$userId] ?? '#aaaaaa'; // Default warna hitam jika user_id tidak ditemukan
    }

}