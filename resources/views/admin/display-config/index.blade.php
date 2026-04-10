@extends('layouts.admin')

@section('content')
<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="font-weight-bold text-nowrap"><i class="fas fa-cog me-2"></i> Konfigurasi Display</h3>
        <a href="{{ route('admin.display-config.create') }}" class="btn btn-success shadow-sm">
            <i class="fas fa-plus me-2"></i>Tambah Config
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-lg">
        <div class="card-body table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Lokasi</th>
                        <th>Mode</th>
                        <th>Konten</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($configs as $config)
                        <tr>
                            <td>{{ $config->location }}</td>

                            <td>
                                <span class="badge bg-{{ $config->mode == 'announcement' ? 'danger' : 'success' }}">
                                    {{ $config->mode }}
                                </span>
                            </td>

                            <td>
                                @if($config->image_path)
                                    <img src="{{ asset('storage/'.$config->image_path) }}" style="height:40px;">
                                @else
                                    {{ Str::limit($config->content_value, 40) }}
                                @endif
                            </td>

                            {{-- 🔥 TOGGLE --}}
                            <td>
                                <form action="{{ route('admin.display-config.toggle', $config->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <label class="toggle-switch">
                                        <input type="checkbox" {{ $config->is_active ? 'checked' : '' }} onchange="this.form.submit()">
                                        <span class="toggle-slider"></span>
                                    </label>
                                </form>
                            </td>

                            <td>
                                <a href="{{ route('admin.display-config.edit', $config->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i>
                                </a>

                                <form action="{{ route('admin.display-config.destroy', $config->id) }}" method="POST" style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger" onclick="return confirm('Yakin?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection