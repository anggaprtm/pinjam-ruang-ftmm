@extends('layouts.admin')
@section('content')
<div class="d-flex align-items-center mb-3"><h3 class="mb-0">Master Ormawa</h3><a href="{{ route('admin.ormawas-master.create') }}" class="btn btn-success ms-auto">Tambah Ormawa</a></div>
<div class="card"><div class="card-body table-responsive">
<table class="table table-bordered"><thead><tr><th>#</th><th>Nama</th><th>Jenis</th><th>Operator</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
@forelse($items as $item)
<tr><td>{{ $item->id }}</td><td>{{ $item->nama }}</td><td>{{ $item->jenisOrmawa->nama_jenis ?? '-' }}</td><td>{{ $item->users->pluck('name')->join(', ') ?: '-' }}</td><td>{{ $item->is_active ? 'Aktif' : 'Nonaktif' }}</td>
<td class="d-flex gap-1"><a class="btn btn-xs btn-warning" href="{{ route('admin.ormawas-master.edit', $item->id) }}">Edit</a><form method="POST" action="{{ route('admin.ormawas-master.destroy', $item->id) }}">@csrf @method('DELETE')<button class="btn btn-xs btn-danger" onclick="return confirm('Hapus?')">Hapus</button></form></td></tr>
@empty <tr><td colspan="6" class="text-center text-muted">Belum ada data.</td></tr>@endforelse
</tbody></table>{{ $items->links() }}
</div></div>
@endsection
