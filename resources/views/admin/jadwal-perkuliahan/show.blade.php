@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.kegiatan.title') }}
    </div>

    <div class="card-body">
        <div class="form-group">
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.jadwal-perkuliahan.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr>
                        <th width="200">
                            ID
                        </th>
                        <td>
                            {{ $jadwalPerkuliahan->id }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            Ruangan
                        </th>
                        <td>
                            {{ $jadwalPerkuliahan->ruangan->nama ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            Mata Kuliah
                        </th>
                        <td>
                            {{ $jadwalPerkuliahan->mata_kuliah }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            Hari
                        </th>
                        <td>
                            {{ $jadwalPerkuliahan->hari }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            Waktu Mulai
                        </th>
                        <td>
                            {{ $jadwalPerkuliahan->waktu_mulai }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            Waktu Selesai
                        </th>
                        <td>
                            {{ $jadwalPerkuliahan->waktu_selesai }}
                        </td>
                    </tr>
                    <tr>
                    </tr>
                    <tr>
                        <th>
                            Berlaku Mulai
                        </th>
                        <td>
                            {{ $jadwalPerkuliahan->berlaku_mulai }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            Berlaku Sampai
                        </th>
                        <td>
                            {{ $jadwalPerkuliahan->berlaku_sampai }}
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.jadwal-perkuliahan.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
