@extends('layouts.admin')
@section('content')
<div class="content">
    <div class="row">
    <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div id="current-time"></div>
                </div>
    <div class="row">
        @php
            use Carbon\Carbon;
        @endphp
        @can('home_access')
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    Kegiatan Hari Ini
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover datatable datatable-Kegiatan">
                            <thead>
                                <tr>
                                    <th width="30" style="text-align: center;">
                                        No
                                    </th>
                                    <th style="text-align: center;">
                                        {{ trans('cruds.kegiatan.fields.nama_kegiatan') }}
                                    </th>
                                    <th width="200" style="text-align: center;">
                                        {{ trans('cruds.kegiatan.fields.ruangan') }}
                                    </th>
                                    <th width="150" style="text-align: center;">
                                        Waktu
                                    </th>
                                    <th width="50" style="text-align: center;">
                                        Peminjam
                                    </th>
                                    <th style="text-align: center;">
                                        Keterangan
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                            @if($kegiatan->isEmpty())
                                <tr>
                                    <td colspan="8" class="text-center">Tidak ada kegiatan hari ini.</td>
                                </tr>
                            @else
                                @foreach($kegiatan as $key => $kegiatan)
                                    <tr class="{{ $kegiatan->is_ongoing ? 'bg-success' : '' }}" data-entry-id="{{ $kegiatan->id }}">
                                        <td style="text-align: center;">
                                            {{ $loop->iteration }}
                                        </td>
                                        <td>
                                            {{ $kegiatan->nama_kegiatan ?? '' }}
                                        </td>
                                        <td style="text-align: center;">
                                            {{ $kegiatan->ruangan->nama ?? '' }}
                                        </td>
                                        <td class="text-center">
                                            {{ Carbon::parse($kegiatan->waktu_mulai)->format('H:i') }} - {{ Carbon::parse($kegiatan->waktu_selesai)->format('H:i') }}
                                        </td>
                                        <td style="text-align: center;">
                                            {{ $kegiatan->user->name ?? '' }}
                                        </td>
                                        <td>
                                            {{ $kegiatan->deskripsi ?? '' }}
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                            </tbody>
                        </table>
                        <div class="calendar-legend" style="margin-top: 20px;">
                            <ul style="list-style: none; padding: 0; display: flex; gap: 15px;">
                                    <li style="display: flex; align-items: center;">
                                        <span style="display: inline-block; width: 10px; height: 10px; background-color: green; margin-right: 5px;"></span>
                                        Kegiatan Sedang Berlangsung
                                    </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endcan
        @can('info_access')
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    Informasi
                </div>
                <div class="card-body">
                    <h2>Cara Melakukan Peminjaman Ruang</h2>
                    <ol>
                        <li>Klik "Cari Ruang"</li>
                        <li>Masukkan waktu mulai dan waktu selesai kegiatan</li>
                        <li>Ketikkan kapasitas ruang</li>
                        <li>Kemudian klik "Cari"</li>
                        <li>Ruang yang tersedia akan muncul dibawahnya</li>
                        <li>Pilih ruang yang tersedia sesuai kebutuhan, kemudian klik "Pinjam Ruang"</li>
                        <li>Masukkan nama kegiatan</li>
                        <li>Masukkan deskripsi/pesan untuk pemroses (opsional)</li>
                        <li>Upload berkas Surat Peminjaman Ruang dengan dilampiri SIK yang sudah ditandatangani Wadek I</li>
                        <li>Klik tombol "OK"</li>
                        <li>Kegiatan berhasil dibuat, dan kegiatan yang sudah diajukan akan muncul di tab "Kegiatan"</li>
                        <li>Harap tunggu proses verifikasi peminjaman dari Operator -> Verif Akademik -> Verif Sarpras</li>
                        <li>Ketika kegiatan telah disetujui, maka Peminjam dapat menuju R.Sarpras Lt.10 untuk melakukan peminjaman barang</li>
                    </ol>
                </div>
            </div>
        </div>
        @endcan
    </div>
</div>
@endsection
@section('scripts')
@parent
@endsection
