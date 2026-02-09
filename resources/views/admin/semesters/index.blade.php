@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Daftar Semester</h4>
        <a class="btn btn-primary" href="{{ route('admin.semesters.create') }}">
            <i class="fas fa-plus me-2"></i> Tambah Semester
        </a>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover datatable">
                <thead>
                    <tr>
                        <th>Nama Semester</th>
                        <th>Tanggal Mulai</th>
                        <th>Tanggal Selesai</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($semesters as $semester)
                        <tr>
                            <td>{{ $semester->nama }}</td>
                            <td>{{ $semester->tanggal_mulai->format('d M Y') }}</td>
                            <td>{{ $semester->tanggal_selesai->format('d M Y') }}</td>
                            <td>
                                @if($semester->is_active)
                                    <span class="badge bg-success">AKTIF</span>
                                @else
                                    <span class="badge bg-secondary">Arsip</span>
                                @endif
                            </td>
                            <td>
                                <a class="btn btn-xs btn-info" href="{{ route('admin.semesters.edit', $semester->id) }}">
                                    Edit
                                </a>
                                <form action="{{ route('admin.semesters.destroy', $semester->id) }}" method="POST" onsubmit="return confirm('Yakin hapus?');" style="display: inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection