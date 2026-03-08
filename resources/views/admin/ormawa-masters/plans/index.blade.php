@extends('layouts.admin')
@section('content')
<div class="d-flex align-items-center mb-3"><h3 class="mb-0">Master Proker Ormawa</h3><a href="{{ route('admin.ormawa-plans.create') }}" class="btn btn-success ms-auto">Tambah Plan</a></div>
<div class="card"><div class="card-body table-responsive">
<table class="table table-bordered"><thead><tr><th>#</th><th>Ormawa</th><th>Tahun</th><th>Status Plan</th><th>Jumlah Proker</th><th>Aksi</th></tr></thead><tbody>
@forelse($plans as $plan)
<tr><td>{{ $plan->id }}</td><td>{{ $plan->ormawa->nama ?? '-' }}</td><td>{{ $plan->tahun }}</td><td>{{ strtoupper($plan->status_plan) }}</td><td>{{ $plan->items->count() }}</td><td class="d-flex gap-1"><a class="btn btn-xs btn-warning" href="{{ route('admin.ormawa-plans.edit', $plan->id) }}">Kelola</a><form method="POST" action="{{ route('admin.ormawa-plans.destroy', $plan->id) }}">@csrf @method('DELETE')<button class="btn btn-xs btn-danger" onclick="return confirm('Hapus?')">Hapus</button></form></td></tr>
@empty <tr><td colspan="6" class="text-center text-muted">Belum ada plan.</td></tr>@endforelse
</tbody></table>{{ $plans->links() }}
</div></div>
@endsection
