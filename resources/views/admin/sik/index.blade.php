@extends('layouts.admin')
@section('content')
<div class="d-flex align-items-center mb-3">
    <h3 class="mb-0">SIK Proker Ormawa</h3>
    <div class="ms-auto">
        <a href="{{ route('admin.sik.create') }}" class="btn btn-success">
            <i class="fas fa-plus-circle me-1"></i> Ajukan SIK
        </a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Ormawa</th>
                    <th>Proker</th>
                    <th>Status</th>
                    <th>Timeline Final</th>
                    <th>No SIK e-office</th>
                    <th>Dibuat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($applications as $item)
                    <tr>
                        <td>{{ $item->id }}</td>
                        <td>{{ $item->ormawa->nama ?? '-' }}</td>
                        <td>{{ $item->judul_final_kegiatan }}</td>
                        <td>
                            <span class="badge bg-{{ $item->status_sik === 'issued' ? 'success' : ($item->status_sik === 'need_revision' ? 'warning' : ($item->status_sik === 'cancelled' ? 'danger' : 'secondary')) }}">
                                {{ strtoupper($item->status_sik) }}
                            </span>
                        </td>
                        <td>{{ optional($item->timeline_mulai_final)->format('d M Y') }} - {{ optional($item->timeline_selesai_final)->format('d M Y') }}</td>
                        <td>{{ $item->nomor_sik_eoffice ?? '-' }}</td>
                        <td>{{ optional($item->created_at)->format('d M Y H:i') }}</td>
                        <td>
                            <a href="{{ route('admin.sik.show', $item->id) }}" class="btn btn-xs btn-info">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if($item->status_sik === 'need_revision')
                                <a href="{{ route('admin.sik.edit', $item->id) }}" class="btn btn-xs btn-warning" title="Revisi Pengajuan">
                                    <i class="fas fa-pen"></i>
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">Belum ada pengajuan SIK.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-3">
            {{ $applications->links() }}
        </div>
    </div>
</div>
@endsection
