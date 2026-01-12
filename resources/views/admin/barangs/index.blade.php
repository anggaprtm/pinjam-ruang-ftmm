@extends('layouts.admin')
@section('content')

{{-- Bagian Header --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="font-weight-bold">Master Barang</h3>
    @can('barang_create')
        <a class="btn btn-success" href="{{ route('admin.barangs.create') }}">
            <i class="fas fa-plus-circle me-2"></i> Tambah Barang
        </a>
    @endcan
</div>

{{-- Tabel Modern --}}
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Barang</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($barangs as $barang)
                        <tr data-entry-id="{{ $barang->id }}">
                            <td>{{ $barang->id ?? '' }}</td>
                            <td>{{ $barang->nama_barang ?? '' }}</td>
                            <td>{{ $barang->stok ?? '' }}</td>
                            <td>
                                @can('barang_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.barangs.show', $barang->id) }}">
                                        View
                                    </a>
                                @endcan

                                @can('barang_edit')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.barangs.edit', $barang->id) }}">
                                        Edit
                                    </a>
                                @endcan

                                @can('barang_delete')
                                    <form action="{{ route('admin.barangs.destroy', $barang->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="submit" class="btn btn-xs btn-danger" value="Delete">
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
