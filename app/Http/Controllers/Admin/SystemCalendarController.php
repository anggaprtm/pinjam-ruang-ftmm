<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kegiatan;
use App\Models\JadwalPerkuliahan;
use App\Models\Ruangan;
use App\Models\User;
use Carbon\Carbon;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
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

        $filterRuangan = $request->input('ruangan_id');
        $filterPeminjam = $request->input('user_id');
        $filterKuliah = $request->input('filter_kuliah', 'non-kuliah');

        if ($filterKuliah == 'kuliah' || $filterKuliah == 'semua') {
            $this->sources[] = [
                'model'      => '\App\Models\JadwalPerkuliahan',
                'date_field' => 'waktu_mulai',
                'end_field'  => 'waktu_selesai',
                'field'      => 'mata_kuliah',
                'prefix'     => '[Kuliah]',
                'suffix'     => '',
                'route'      => 'admin.jadwal-perkuliahan.edit',
            ];
        }

        foreach ($this->sources as $source) {
            $query = $source['model']::query();

            if ($source['model'] == '\App\Models\JadwalPerkuliahan') {
                // apply filters for ruangan and peminjam if provided
                if ($filterRuangan) {
                    $query->where('ruangan_id', $filterRuangan);
                }
                if ($filterPeminjam) {
                    // JadwalPerkuliahan may not have user_id; skip if not applicable
                    if (Schema::hasColumn((new JadwalPerkuliahan)->getTable(), 'user_id')) {
                        $query->where('user_id', $filterPeminjam);
                    }
                }

                $jadwalList = $query->get();
                foreach ($jadwalList as $model) {
                    $startDate = Carbon::parse($model->berlaku_mulai);
                    $endDate = Carbon::parse($model->berlaku_sampai);
                    $targetDay = strtolower($model->hari);

                    for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                        if (strtolower($date->locale('id')->isoFormat('dddd')) !== $targetDay) {
                            continue;
                        }
                        $startDateTime = Carbon::parse($date->toDateString() . ' ' . $model->waktu_mulai);
                        $endDateTime = Carbon::parse($date->toDateString() . ' ' . $model->waktu_selesai);

                        $events[] = [
                            'id' => 'kuliah-' . $model->id,
                            'title' => trim($source['prefix'] . " " . $model->{$source['field']} . " " . $source['suffix']),
                            'start' => $startDateTime,
                            'end' => $endDateTime,
                            'color' => '#17a2b8',
                            'extendedProps' => [
                                'ruangan_nama' => $model->ruangan->nama,
                                'user_name' => $model->program_studi ?? '',
                                'deskripsi' => '',
                                'type' => 'perkuliahan'
                            ],
                            'url' => route($source['route'], $model->id),
                        ];
                    }
                }
                continue;
            }

            if ($filterKuliah == 'kuliah') {
                continue;
            }

            $query->where('status', 'disetujui');
            if ($filterRuangan) {
                $query->where('ruangan_id', $filterRuangan);
            }
            if ($filterPeminjam) {
                $query->where('user_id', $filterPeminjam);
            }

            foreach ($query->get() as $model) {
                $startDate = $model->getAttributes()[$source['date_field']];
                $endDate = $model->getAttributes()[$source['end_field']];
                if (!$startDate || !$endDate) {
                    continue;
                }
                $color = $this->getUserColor($model->user_id);
                $events[] = [
                    'id' => $model->id,
                    'title' => trim($source['prefix'] . " " . $model->{$source['field']} . " " . $source['suffix']),
                    'start' => $startDate,
                    'end' => $endDate,
                    'color' => $color,
                    'extendedProps' => [
                        'ruangan_nama' => $model->ruangan->nama,
                        'user_name' => $model->user->name,
                        'deskripsi' => $model->deskripsi,
                        'nama_pic' => $model->nama_pic,
                        'nomor_telepon' => $model->nomor_telepon,
                        'type' => 'kegiatan'
                    ],
                    'url' => route($source['route'], $model->id),
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
            1 => '#1E90FF', 2 => '#9e0142', 3 => '#095259', 4 => '#f46d43',
            5 => '#fdae61', 6 => '#fee08b', 7 => '#000000', 8 => '#abdda4',
            9 => '#66c2a5', 10 => '#3288bd', 11 => '#5e4fa2', 12 => '#a4c2f4',
            13 => '#b2b200', 14 => '#00ff00', 15 => '#d5a6bd', 16 => '#ea9999',
        ];
        return $colors[$userId] ?? '#741847';
    }
}
