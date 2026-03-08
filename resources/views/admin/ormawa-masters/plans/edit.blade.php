@extends('layouts.admin')
@section('content')
<h3>Kelola Plan Proker #{{ $ormawaPlan->id }}</h3>
<form method="POST" action="{{ route('admin.ormawa-plans.update', $ormawaPlan->id) }}">@csrf @method('PUT')
<div class="card mb-3"><div class="card-body row g-3">
<div class="col-md-5"><label>Ormawa</label><select class="form-control" name="ormawa_id" required>@foreach($ormawas as $ormawa)<option value="{{ $ormawa->id }}" @selected($ormawaPlan->ormawa_id == $ormawa->id)>{{ $ormawa->nama }}</option>@endforeach</select></div>
<div class="col-md-3"><label>Tahun</label><input type="number" class="form-control" name="tahun" value="{{ $ormawaPlan->tahun }}" required></div>
<div class="col-md-4"><label>Status Plan</label><select class="form-control" name="status_plan" required>@foreach(['draft','published','locked'] as $status)<option value="{{ $status }}" @selected($ormawaPlan->status_plan === $status)>{{ strtoupper($status) }}</option>@endforeach</select></div>
<div class="col-md-12"><button class="btn btn-primary">Update Plan</button></div>
</div></div>
</form>

<div class="card mb-3"><div class="card-header"><strong>Tambah Item Proker</strong></div><div class="card-body">
<form method="POST" action="{{ route('admin.ormawa-plans.items.store', $ormawaPlan->id) }}">@csrf
<div class="row g-2"><div class="col-md-2"><input class="form-control" name="kode_proker" placeholder="Kode"></div><div class="col-md-4"><input class="form-control" name="nama_rencana" placeholder="Nama rencana" required></div><div class="col-md-2"><input type="date" class="form-control" name="timeline_mulai_rencana"></div><div class="col-md-2"><input type="date" class="form-control" name="timeline_selesai_rencana"></div><div class="col-md-2"><button class="btn btn-success w-100">Tambah</button></div><div class="col-md-12"><textarea class="form-control" name="deskripsi_rencana" rows="2" placeholder="Deskripsi"></textarea></div></div>
</form>
</div></div>

<div class="card"><div class="card-header"><strong>Daftar Item Proker</strong></div><div class="card-body table-responsive">
<table class="table table-bordered table-sm"><thead><tr><th>#</th><th>Kode</th><th>Nama</th><th>Timeline</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
@forelse($ormawaPlan->items as $item)
<tr><td>{{ $item->id }}</td>
<td><form method="POST" action="{{ route('admin.ormawa-plans.items.update', [$ormawaPlan->id, $item->id]) }}">@csrf @method('PUT')<input class="form-control form-control-sm" name="kode_proker" value="{{ $item->kode_proker }}"></td>
<td><input class="form-control form-control-sm" name="nama_rencana" value="{{ $item->nama_rencana }}" required></td>
<td><div class="d-flex gap-1"><input type="date" class="form-control form-control-sm" name="timeline_mulai_rencana" value="{{ $item->timeline_mulai_rencana }}"><input type="date" class="form-control form-control-sm" name="timeline_selesai_rencana" value="{{ $item->timeline_selesai_rencana }}"></div></td>
<td><select class="form-control form-control-sm" name="status_item">@foreach(['belum_diajukan','diajukan','proses','sik_terbit','ditolak','arsip'] as $st)<option value="{{ $st }}" @selected($item->status_item === $st)>{{ $st }}</option>@endforeach</select></td>
<td class="d-flex gap-1"><input type="hidden" name="deskripsi_rencana" value="{{ $item->deskripsi_rencana }}"><button class="btn btn-xs btn-primary">Update</button></form><form method="POST" action="{{ route('admin.ormawa-plans.items.destroy', [$ormawaPlan->id, $item->id]) }}">@csrf @method('DELETE')<button class="btn btn-xs btn-danger" onclick="return confirm('Hapus item?')">Hapus</button></form></td></tr>
@empty<tr><td colspan="6" class="text-center text-muted">Belum ada item.</td></tr>@endforelse
</tbody></table>
</div></div>
@endsection
