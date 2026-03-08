@extends('layouts.admin')
@section('content')
<div class="d-flex align-items-center mb-3">
    <h3 class="mb-0">Master Flow Verifikasi SIK</h3>
    <div class="ms-auto">
        <a href="{{ route('admin.sik-flows.create') }}" class="btn btn-success">
            <i class="fas fa-plus-circle me-1"></i> Tambah Flow
        </a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Jenis Ormawa</th>
                    <th>Nama Flow</th>
                    <th>Status</th>
                    <th>Total Step</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($flows as $flow)
                    <tr>
                        <td>{{ $flow->id }}</td>
                        <td>{{ $flow->jenisOrmawa->nama_jenis ?? '-' }}</td>
                        <td>{{ $flow->nama_flow }}</td>
                        <td>
                            <span class="badge bg-{{ $flow->is_active ? 'success' : 'secondary' }}">
                                {{ $flow->is_active ? 'AKTIF' : 'NONAKTIF' }}
                            </span>
                        </td>
                        <td>{{ $flow->steps->count() }}</td>
                        <td class="d-flex gap-1">
                            <a href="{{ route('admin.sik-flows.edit', $flow->id) }}" class="btn btn-xs btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.sik-flows.destroy', $flow->id) }}" method="POST" onsubmit="return confirm('Hapus flow ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">Belum ada flow verifikasi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-3">{{ $flows->links() }}</div>
    </div>
</div>
@endsection
