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
                        <th width="200">
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
                            Peminjam
                        </th>
                        <td>
                            {{ $kegiatan->user->name ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            Nomor Telepon PIC
                        </th>
                        <td>
                            {{ $kegiatan->nomor_telepon ?? '' }}
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
                                Tidak Ada Surat Izin/Diproses Oleh Admin
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>
                            Status
                        </th>
                        <td>
                            @if($kegiatan->status == 'belum_disetujui')
                                <span class="badge badge-warning">Menunggu Verif Operator</span>
                            @elseif($kegiatan->status == 'verifikasi_sarpras')
                                <span class="badge badge-warning">Menunggu Verif Akademik</span>
                            @elseif($kegiatan->status == 'verifikasi_akademik')
                                <span class="badge badge-warning">Menunggu Verif Sarpras</span>
                            @elseif($kegiatan->status == 'disetujui')
                                <span class="badge badge-success">Disetujui</span>
                            @elseif($kegiatan->status == 'ditolak')
                                <span class="badge badge-danger">Ditolak</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>
                            Timestamp Verifikasi
                        </th>
                        <td>
                            @if ($kegiatan->status == 'belum_disetujui')
                                <span>Verif Operator: -</span>
                            @elseif ($kegiatan->status == 'verifikasi_sarpras')
                                <span>Verif Operator: {{ $kegiatan->verifikasi_sarpras_at ? \Carbon\Carbon::parse($kegiatan->verifikasi_sarpras_at)->format('d/m/y H:i:s') : '-' }}</span>
                            @elseif ($kegiatan->status == 'verifikasi_akademik')    
                                <span>Verif Operator: {{ $kegiatan->verifikasi_sarpras_at ? \Carbon\Carbon::parse($kegiatan->verifikasi_sarpras_at)->format('d/m/y H:i:s') : '-' }}</span><br>
                                <span>Verif AMA: {{ $kegiatan->verifikasi_akademik_at ? \Carbon\Carbon::parse($kegiatan->verifikasi_akademik_at)->format('d/m/y H:i:s') : '-' }}</span>
                            @elseif ($kegiatan->status == 'disetujui')
                                <span>Verif  Operator: {{ $kegiatan->verifikasi_sarpras_at ? \Carbon\Carbon::parse($kegiatan->verifikasi_sarpras_at)->format('d/m/y H:i:s') : '-' }}</span><br>
                                <span>Verif AMA: {{ $kegiatan->verifikasi_akademik_at ? \Carbon\Carbon::parse($kegiatan->verifikasi_akademik_at)->format('d/m/y H:i:s') : '-' }}</span><br>
                                <span>Disetujui: {{ $kegiatan->disetujui_at ? \Carbon\Carbon::parse($kegiatan->disetujui_at)->format('d/m/y H:i:s') : '-' }}</span>
                            @elseif ($kegiatan->status == 'ditolak')
                                <span>Ditolak: {{ $kegiatan->ditolak_at ? \Carbon\Carbon::parse($kegiatan->ditolak_at)->format('d/m/y H:i:s') : '-' }}</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>
                            Komentar Pemroses
                        </th>
                        <td>
                            {{ $kegiatan->notes ?? '-' }}
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
