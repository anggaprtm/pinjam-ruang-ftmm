@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="h3 mb-0 text-gray-800 fw-bold">Master Jadwal Kerja</h3>
        <a href="{{ route('admin.periode-jam-kerja.create') }}" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus me-1"></i> Tambah Periode
        </a>
    </div>

    @if(session('message'))
        <div class="alert alert-success">{{ session('message') }}</div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th class="text-dark">Nama Periode</th>
                            <th class="text-dark">Tanggal Berlaku</th>
                            <th class="text-dark">Jam Masuk</th>
                            <th class="text-dark">Pulang (Senin-Kamis)</th>
                            <th class="text-dark">Pulang (Jumat)</th>
                            <th class="text-dark text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($periodes as $p)
                        <tr>
                            <td class="fw-bold">{{ $p->nama_periode }}</td>
                            <td>{{ \Carbon\Carbon::parse($p->tanggal_mulai)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($p->tanggal_selesai)->format('d/m/Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($p->jam_masuk)->format('H:i') }}</td>
                            <td>{{ \Carbon\Carbon::parse($p->jam_pulang_senin_kamis)->format('H:i') }}</td>
                            <td>{{ \Carbon\Carbon::parse($p->jam_pulang_jumat)->format('H:i') }}</td>
                            <td class="text-center">
                                <a href="{{ route('admin.periode-jam-kerja.edit', $p->id) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.periode-jam-kerja.destroy', $p->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus data ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">Belum ada data periode jam kerja.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection