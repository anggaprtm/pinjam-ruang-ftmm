@extends('layouts.admin')
@section('content')
<h3>Tambah Plan Proker</h3>
<form method="POST" action="{{ route('admin.ormawa-plans.store') }}">@csrf
<div class="card"><div class="card-body row g-3">
<div class="col-md-5"><label>Ormawa</label><select class="form-control" name="ormawa_id" required><option value="">--pilih--</option>@foreach($ormawas as $ormawa)<option value="{{ $ormawa->id }}">{{ $ormawa->nama }}</option>@endforeach</select></div>
<div class="col-md-3"><label>Tahun</label><input type="number" class="form-control" name="tahun" value="{{ now()->year }}" required></div>
<div class="col-md-4"><label>Status Plan</label><select class="form-control" name="status_plan" required><option value="draft">Draft</option><option value="published">Published</option><option value="locked">Locked</option></select></div>
</div></div>
<button class="btn btn-primary mt-3">Simpan</button>
</form>
@endsection
