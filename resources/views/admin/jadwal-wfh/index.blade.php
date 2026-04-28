@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="h3 mb-0 text-gray-800 fw-bold">Master Jadwal WFH</h3>
            <p class="text-muted small mb-0">Kelola jadwal Work From Home massal maupun spesifik per pegawai.</p>
        </div>
        <a href="{{ route('admin.jadwal-wfh.create') }}" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus me-1"></i> Tambah Jadwal
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm"><i class="fas fa-check-circle me-1"></i> {{ session('success') }}</div>
    @endif

    <div class="card shadow mb-4 border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th class="text-dark">Keterangan</th>
                            <th class="text-dark">Tipe Jadwal</th>
                            <th class="text-dark">Waktu Berlaku</th>
                            <th class="text-dark">Sasaran Pegawai</th>
                            <th class="text-dark text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $namaHari = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
                        @endphp
                        
                        @forelse($jadwals as $j)
                        <tr>
                            <td class="fw-bold">{{ $j->keterangan ?? '-' }}</td>
                            <td>
                                @if($j->hari_rutin)
                                    <span class="badge bg-info text-white rounded-pill"><i class="fas fa-redo me-1"></i> Rutin Mingguan</span>
                                @elseif($j->tanggal)
                                    <span class="badge bg-warning text-dark rounded-pill"><i class="fas fa-calendar-day me-1"></i> Insidental</span>
                                @endif
                            </td>
                            <td>
                                @if($j->hari_rutin)
                                    Setiap <b>Hari {{ $namaHari[$j->hari_rutin] ?? 'Tidak valid' }}</b>
                                @elseif($j->tanggal)
                                    <b>{{ \Carbon\Carbon::parse($j->tanggal)->translatedFormat('d F Y') }}</b>
                                @endif
                            </td>
                            <td>
                                @if($j->is_global)
                                    <span class="badge bg-success rounded-pill px-3"><i class="fas fa-users me-1"></i> Semua Pegawai</span>
                                @else
                                    <span class="badge bg-primary rounded-pill px-3"><i class="fas fa-user-check me-1"></i> {{ $j->users->count() }} Pegawai</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.jadwal-wfh.edit', $j->id) }}" class="btn btn-sm btn-light border shadow-sm text-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.jadwal-wfh.destroy', $j->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus jadwal WFH ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-light border shadow-sm text-danger" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="fas fa-calendar-times d-block mb-2" style="font-size: 2rem; color: #cbd5e1;"></i>
                                Belum ada jadwal WFH yang diatur.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection