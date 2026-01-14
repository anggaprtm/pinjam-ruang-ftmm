@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        Detail Barang
    </div>

    <div class="card-body">
        <div class="form-group">
            <a class="btn btn-default" href="{{ route('admin.barangs.master') }}">
                Kembali ke Daftar
            </a>
        </div>
        <table class="table table-bordered table-striped">
            <tbody>
                <tr>
                    <th>ID</th>
                    <td>{{ $barang->id }}</td>
                </tr>
                <tr>
                    <th>Nama Barang</th>
                    <td>{{ $barang->nama_barang }}</td>
                </tr>
                <tr>
                    <th>Deskripsi</th>
                    <td>{{ $barang->deskripsi }}</td>
                </tr>
                <tr>
                    <th>Stok</th>
                    <td>{{ $barang->stok }}</td>
                </tr>
                <tr>
                    <th>Foto</th>
                    <td>
                        @if($barang->foto)
                            <img src="{{ asset('storage/' . $barang->foto) }}" alt="{{ $barang->nama_barang }}" class="img-thumbnail" width="200">
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="form-group">
            <a class="btn btn-default" href="{{ route('admin.barangs.master') }}">
                Kembali ke Daftar
            </a>
        </div>
    </div>
</div>

@endsection
