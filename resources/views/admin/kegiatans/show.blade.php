@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.kegiatan.title') }}
    </div>

    <div class="card-body">
        <div class="form-group">
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.kegiatans.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr>
                        <th>
                            {{ trans('cruds.kegiatan.fields.id') }}
                        </th>
                        <td>
                            {{ $kegiatan->id }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.kegiatan.fields.ruangan') }}
                        </th>
                        <td>
                            {{ $kegiatan->ruangan->nama ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.kegiatan.fields.nama_kegiatan') }}
                        </th>
                        <td>
                            {{ $kegiatan->nama_kegiatan }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.kegiatan.fields.waktu_mulai') }}
                        </th>
                        <td>
                            {{ $kegiatan->waktu_mulai }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.kegiatan.fields.waktu_selesai') }}
                        </th>
                        <td>
                            {{ $kegiatan->waktu_selesai }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.kegiatan.fields.deskripsi') }}
                        </th>
                        <td>
                            {{ $kegiatan->deskripsi }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.kegiatan.fields.user') }}
                        </th>
                        <td>
                            {{ $kegiatan->user->name ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            Surat Izin
                        </th>
                        <td>
                            @if ($kegiatan->surat_izin)
                                <a href="{{ asset('storage/' . $kegiatan->surat_izin) }}" class="btn btn-success" target="_blank">
                                    Lihat Surat Izin
                                </a>
                            @else
                                Tidak Ada Surat Izin
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.kegiatans.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
        </div>
    </div>
</div>



@endsection