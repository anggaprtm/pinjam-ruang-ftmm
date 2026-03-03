@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="h3 mb-0 text-gray-800 fw-bold"><i class="fas fa-chalkboard-teacher me-2"></i>Manajemen Data Dosen</h3>
        <a href="{{ route('admin.dosen.create') }}" class="btn btn-primary shadow-sm fw-bold">
            <i class="fas fa-plus me-1"></i> Tambah Dosen
        </a>
    </div>

    @if(session('message'))
        <div class="alert alert-success shadow-sm">{{ session('message') }}</div>
    @endif

    <div class="card shadow mb-4 border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Nama Dosen</th>
                            <th>NIP / NIDN</th>
                            <th>Jabatan / Golongan</th>
                            <th>Homebase</th>
                            <th class="text-center">Status</th>
                            <th class="text-center pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dosens as $d)
                        <tr>
                            <td class="ps-4 fw-bold text-dark">
                                {{ $d->dosenDetail->nama_lengkap_gelar ?? $d->name }}
                            </td>
                            <td>
                                <div><small class="text-muted">NIP:</small> {{ $d->nip ?? '-' }}</div>
                                <div><small class="text-muted">NIDN:</small> {{ $d->dosenDetail->nidn ?? '-' }}</div>
                            </td>
                            <td>
                                <div>{{ $d->dosenDetail->jabatan_fungsional ?? '-' }}</div>
                                <div class="small text-muted">{{ $d->dosenDetail->pangkat_golongan ?? '-' }}</div>
                            </td>
                            <td>{{ $d->dosenDetail->homebase_prodi ?? '-' }}</td>
                            <td class="text-center">
                                @php
                                    $status = $d->dosenDetail->status_keaktifan ?? 'Aktif';
                                    $badgeClass = 'bg-success';
                                    if($status == 'Tugas Belajar') $badgeClass = 'bg-info';
                                    elseif($status == 'Cuti' || $status == 'Izin') $badgeClass = 'bg-warning text-dark';
                                    elseif($status == 'Pensiun') $badgeClass = 'bg-secondary';
                                @endphp
                                <span class="badge {{ $badgeClass }} rounded-pill px-3">{{ $status }}</span>
                            </td>
                            <td class="text-center pe-4">
                                <a href="{{ route('admin.dosen.edit', $d->id) }}" class="btn btn-sm btn-warning shadow-sm" title="Edit Data">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.dosen.destroy', $d->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus dosen ini? Data absensi terkait juga bisa terpengaruh.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger shadow-sm" title="Hapus Data">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">Belum ada data dosen.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection