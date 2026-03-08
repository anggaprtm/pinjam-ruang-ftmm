@extends('layouts.admin')
@section('content')
<div class="d-flex align-items-center mb-3">
    <h3 class="mb-0">Ajukan SIK</h3>
    <div class="ms-auto">
        <a href="{{ route('admin.sik.index') }}" class="btn btn-secondary">Kembali</a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form action="{{ route('admin.sik.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Pilih Proker Tahun Aktif ({{ $year }})</label>
                    <select name="program_item_id" class="form-control" required>
                        <option value="">-- Pilih Proker --</option>
                        @foreach($activeProgramItems as $item)
                            <option value="{{ $item->id }}">{{ $item->plan->ormawa->nama ?? '-' }} - {{ $item->nama_rencana }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Judul Final Kegiatan</label>
                    <input type="text" name="judul_final_kegiatan" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Timeline Mulai Final</label>
                    <input type="date" name="timeline_mulai_final" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Timeline Selesai Final</label>
                    <input type="date" name="timeline_selesai_final" class="form-control" required>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Rencana Tempat</label>
                    <input type="text" name="rencana_tempat" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Proposal</label>
                    <input type="file" name="proposal" class="form-control" accept=".pdf,.doc,.docx">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Surat Permohonan</label>
                    <input type="file" name="surat_permohonan" class="form-control" accept=".pdf,.doc,.docx">
                </div>
            </div>
            <div class="mt-4">
                <button class="btn btn-primary" type="submit">Ajukan SIK</button>
            </div>
        </form>
    </div>
</div>
@endsection
