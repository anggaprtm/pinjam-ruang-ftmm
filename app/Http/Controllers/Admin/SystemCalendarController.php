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

                // === HANDLE JADWAL KULIAH ===
                if ($source['model'] == '\App\Models\JadwalPerkuliahan') {
                    $jadwalList = $query->get();

                    foreach ($jadwalList as $model) {
                        $startDate = Carbon::parse($model->berlaku_mulai);
                        $endDate = Carbon::parse($model->berlaku_sampai);
                        $targetDay = strtolower($model->hari); // misal: 'senin', 'jumat'

                        // Loop dari tanggal mulai ke tanggal akhir
                        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                            if (strtolower($date->locale('id')->isoFormat('dddd')) !== $targetDay) {
                                continue; // skip kalau bukan harinya
                            }

                            // Gabungkan tanggal dengan waktu
                            $startDateTime = Carbon::parse($date->toDateString() . ' ' . $model->waktu_mulai);
                            $endDateTime = Carbon::parse($date->toDateString() . ' ' . $model->waktu_selesai);

                            $events[] = [
                                'id' => 'kuliah-' . $model->id,
                                'title' => trim($source['prefix'] . " " . $model->{$source['field']} . " " . $source['suffix'] . " | (" . $model->ruangan->nama . ")"),
                                'start' => $startDateTime,
                                'end' => $endDateTime,
                                'color' => '#4b0082',
                                'extendedProps' => [
                                    'ruangan_nama' => $model->ruangan->nama,
                                    'user_name' => $model->program_studi ?? '',
                                    'deskripsi' => '',
                                ],
                                'url' => route($source['route'], $model->id),
                            ];
                        }
                    }

                    continue; // skip ke source selanjutnya (kegiatan)
                }

                // === HANDLE KEGIATAN NON-KULIAH ===
                if ($filterKuliah == 'kuliah') {
                    continue; // skip kegiatan jika hanya filter kuliah
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
                        'title' => trim($source['prefix'] . " " . $model->{$source['field']} . " " . $source['suffix'] . " | (" . $model->ruangan->nama . ")"),
                        'start' => $startDate,
                        'end' => $endDate,
                        'extendedProps' => [
                            'ruangan_nama' => $model->ruangan->nama,
                            'user_name' => $model->user->name,
                            'deskripsi' => $model->deskripsi,
                            'color' => $color,
                        ],
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
            3 => '#095259', // HIMATERA
            4 => '#f46d43', // HIMANO
            5 => '#fdae61', // HMTI
            6 => '#fee08b', // IME
            7 => '#000000', // BEM FTMM
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