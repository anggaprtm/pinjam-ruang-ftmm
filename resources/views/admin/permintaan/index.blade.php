@extends('layouts.admin')
@section('content')

<div class="d-flex align-items-center mb-4">
    <h3 class="font-weight-bold mb-0">Daftar Permintaan Layanan</h3>
    <div class="ms-auto">
        <a class="btn btn-success" href="{{ route('admin.permintaan-kegiatan.create') }}">
            <i class="fas fa-plus-circle me-2"></i> Ajukan Baru
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped datatable">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Kegiatan</th>
                        <th>PIC</th>
                        <th class="text-center">Status Ruang</th>
                        <th class="text-center">Status Konsumsi</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($permintaans as $item)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($item->tanggal_kegiatan)->format('d M Y') }}</td>
                        <td>
                            <div class="fw-bold">{{ $item->nama_kegiatan }}</div>
                            <small class="text-muted">{{ $item->waktu_mulai }} - {{ $item->waktu_selesai }}</small>
                        </td>
                        <td>{{ $item->picUser->name }}</td>
                        <td class="text-center">
                            @if($item->status_ruang == 'pending') <span class="badge bg-warning text-dark">Pending</span>
                            @elseif($item->status_ruang == 'selesai') <span class="badge bg-success">Selesai</span>
                            @else <span class="badge bg-secondary">-</span> @endif
                        </td>
                        <td class="text-center">
                            @if($item->status_konsumsi == 'pending') <span class="badge bg-warning text-dark">Pending</span>
                            @elseif($item->status_konsumsi == 'selesai') <span class="badge bg-success">Selesai</span>
                            @else <span class="badge bg-secondary">-</span> @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('admin.permintaan-kegiatan.show', $item->id) }}" class="btn btn-sm btn-info text-white">
                                <i class="fas fa-eye"></i> Detail
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection