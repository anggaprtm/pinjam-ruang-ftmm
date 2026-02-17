@extends('layouts.admin')

@section('content')
<div class="content">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="h3 mb-1 text-gray-800 fw-bold">Atur Hari Libur</h3>
            <p class="text-muted small mb-0">Kelola daftar tanggal merah dan cuti bersama.</p>
        </div>
        <a href="{{ route('admin.absensi.index') }}" class="btn btn-secondary shadow-sm" style="border-radius: 10px;">
            <i class="fas fa-arrow-left me-1"></i> Kembali ke Presensi
        </a>
    </div>

    <div class="row">
        {{-- KOLOM KIRI: FORM TAMBAH --}}
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm" style="border-radius: 14px;">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="m-0 fw-bold text-primary"><i class="fas fa-plus-circle me-2"></i>Tambah Hari Libur</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.hari-libur.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold text-gray-700">Tanggal</label>
                            <input type="date" name="tanggal" class="form-control bg-light border-0" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold text-gray-700">Keterangan</label>
                            <input type="text" name="keterangan" class="form-control bg-light border-0" placeholder="Contoh: Hari Raya Idul Fitri" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 fw-bold" style="border-radius: 10px;">
                            <i class="fas fa-save me-1"></i> Simpan
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: TABEL DAFTAR HARI LIBUR --}}
        <div class="col-md-8">
            <div class="card border-0 shadow-sm" style="border-radius: 14px;">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="m-0 fw-bold text-primary"><i class="fas fa-list me-2"></i>Daftar Hari Libur Nasional</h6>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="bg-light text-muted" style="font-size: 0.8rem; text-transform: uppercase;">
                            <tr>
                                <th class="ps-4">Tanggal</th>
                                <th>Keterangan</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($liburs as $libur)
                                <tr>
                                    <td class="ps-4 fw-bold text-dark">
                                        {{ \Carbon\Carbon::parse($libur->tanggal)->locale('id')->isoFormat('D MMMM YYYY') }}
                                        @if(\Carbon\Carbon::parse($libur->tanggal)->isPast())
                                            <span class="badge bg-secondary ms-1" style="font-size: 0.6rem;">Terlewati</span>
                                        @endif
                                    </td>
                                    <td>{{ $libur->keterangan }}</td>
                                    <td class="text-center">
                                        <form action="{{ route('admin.hari-libur.destroy', $libur->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus hari libur ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" style="border-radius: 8px;">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">Belum ada data hari libur yang didaftarkan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection