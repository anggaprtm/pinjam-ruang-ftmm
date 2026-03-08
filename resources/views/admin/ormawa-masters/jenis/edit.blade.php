@extends('layouts.admin')
@section('content')
<h3>Edit Jenis Ormawa</h3>
<form method="POST" action="{{ route('admin.jenis-ormawas.update', $item->id) }}">@csrf @method('PUT')
<div class="card"><div class="card-body row g-3">
<div class="col-md-6"><label>Nama</label><input class="form-control" name="nama_jenis" value="{{ old('nama_jenis', $item->nama_jenis) }}" required></div>
<div class="col-md-4"><label>Kode</label><input class="form-control" name="kode" value="{{ old('kode', $item->kode) }}"></div>
<div class="col-md-2"><label>Status</label><div><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $item->is_active))> Aktif</div></div>
</div></div>
<button class="btn btn-primary mt-3">Update</button>
</form>
@endsection
