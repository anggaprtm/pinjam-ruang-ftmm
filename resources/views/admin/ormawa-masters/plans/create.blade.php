@extends('layouts.admin')
@section('content')
<h3>Tambah Plan Proker</h3>
<form method="POST" action="{{ route('admin.ormawa-plans.store') }}">@csrf
<div class="card mb-3"><div class="card-body row g-3">
<div class="col-md-5"><label>Ormawa</label><select class="form-control" name="ormawa_id" required><option value="">--pilih--</option>@foreach($ormawas as $ormawa)<option value="{{ $ormawa->id }}">{{ $ormawa->nama }}</option>@endforeach</select></div>
<div class="col-md-3"><label>Tahun</label><input type="number" class="form-control" name="tahun" value="{{ now()->year }}" required></div>
<div class="col-md-4"><label>Status Plan</label><select class="form-control" name="status_plan" required><option value="draft">Draft</option><option value="published">Published</option><option value="locked">Locked</option></select></div>
</div></div>

<div class="card"><div class="card-header"><strong>Proker Awal (opsional)</strong></div><div class="card-body row g-3">
<div class="col-md-2"><label>Kode Proker</label><input class="form-control" name="kode_proker"></div>
<div class="col-md-4"><label>Nama Proker</label><input class="form-control" name="nama_rencana" placeholder="Contoh: Seminar Nasional"></div>
<div class="col-md-2"><label>Timeline Mulai</label><input type="date" class="form-control" name="timeline_mulai_rencana"></div>
<div class="col-md-2"><label>Timeline Selesai</label><input type="date" class="form-control" name="timeline_selesai_rencana"></div>
<div class="col-md-12"><label>Keterangan</label><textarea class="form-control" name="deskripsi_rencana" rows="2"></textarea></div>
</div></div>

<button class="btn btn-primary mt-3">Simpan</button>
</form>
@endsection
