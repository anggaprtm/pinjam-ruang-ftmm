@extends('layouts.admin')
@section('content')
<div class="d-flex align-items-center mb-3">
    <h3 class="mb-0">Revisi Pengajuan SIK: {{ $sikApplication->programItem->nama_rencana ?? $sikApplication->judul_final_kegiatan }}</h3>
    <div class="ms-auto">
        <a href="{{ route('admin.sik.show', $sikApplication->id) }}" class="btn btn-secondary">Kembali</a>
    </div>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="alert alert-warning mb-0">
            <strong>Catatan Revisi:</strong> {{ $sikApplication->catatan_terakhir ?? 'Silakan sesuaikan data dan unggah ulang dokumen bila diperlukan.' }}
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form action="{{ route('admin.sik.update', $sikApplication->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Judul Final Kegiatan</label>
                    <input type="text" name="judul_final_kegiatan" class="form-control" value="{{ old('judul_final_kegiatan', $sikApplication->judul_final_kegiatan) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Rencana Tempat</label>
                    <input type="text" name="rencana_tempat" class="form-control" value="{{ old('rencana_tempat', $sikApplication->rencana_tempat) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Timeline Mulai Final</label>
                    <input type="date" name="timeline_mulai_final" class="form-control" value="{{ old('timeline_mulai_final', optional($sikApplication->timeline_mulai_final)->format('Y-m-d')) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Timeline Selesai Final</label>
                    <input type="date" name="timeline_selesai_final" class="form-control" value="{{ old('timeline_selesai_final', optional($sikApplication->timeline_selesai_final)->format('Y-m-d')) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Proposal (unggah ulang jika revisi dokumen)</label>
                    <input type="file" name="proposal" class="form-control" accept=".pdf,.doc,.docx">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Surat Permohonan (unggah ulang jika revisi dokumen)</label>
                    <input type="file" name="surat_permohonan" class="form-control" accept=".pdf,.doc,.docx">
                </div>
            </div>
            <div class="mt-4">
                <button class="btn btn-primary" type="submit">Kirim Ulang Revisi</button>
            </div>
        </form>
    </div>
</div>
@endsection
