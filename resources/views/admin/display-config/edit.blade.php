@extends('layouts.admin')

@section('content')
<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold">Edit Signage Config</h3>
        <a href="{{ route('admin.display-config.index') }}" class="btn btn-secondary shadow-sm">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-lg">
        <div class="card-body">

            <form method="POST" action="{{ route('admin.display-config.update', $displayConfig->id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label fw-bold">Lokasi</label>
                    <input type="text" name="location" class="form-control"
                        value="{{ old('location', $displayConfig->location) }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Mode</label>
                    <select name="mode" class="form-control">
                        <option value="dashboard" {{ $displayConfig->mode == 'dashboard' ? 'selected' : '' }}>Dashboard</option>
                        <option value="announcement" {{ $displayConfig->mode == 'announcement' ? 'selected' : '' }}>Announcement</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Content Type</label>
                    <select name="content_type" class="form-control">
                        <option value="image" {{ $displayConfig->content_type == 'image' ? 'selected' : '' }}>Image</option>
                        <option value="text" {{ $displayConfig->content_type == 'text' ? 'selected' : '' }}>Text</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Content Value</label>
                    <textarea name="content_value" class="form-control">{{ old('content_value', $displayConfig->content_value) }}</textarea>
                </div>

                {{-- 🔥 UPLOAD --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">Upload Image</label>
                    <input type="file" name="image" class="form-control">
                </div>

                {{-- 🔥 PREVIEW --}}
                @if($displayConfig->image_path)
                    <div class="mb-3">
                        <label class="form-label fw-bold">Preview Upload</label>
                        <div class="border rounded p-2 text-center">
                            <img src="{{ asset('storage/'.$displayConfig->image_path) }}" style="max-height:200px;">
                        </div>
                    </div>
                @elseif($displayConfig->content_type === 'image')
                    <div class="mb-3">
                        <label class="form-label fw-bold">Preview URL</label>
                        <div class="border rounded p-2 text-center">
                            <img src="{{ $displayConfig->content_value }}" style="max-height:200px;">
                        </div>
                    </div>
                @endif

                <div class="d-flex gap-2">
                    <button class="btn btn-primary shadow-sm">
                        <i class="fas fa-save me-1"></i> Update
                    </button>

                    <a href="{{ route('admin.display-config.index') }}" class="btn btn-light">
                        Batal
                    </a>
                </div>

            </form>

            <hr class="my-4">

            <h5 class="fw-bold">Schedule Mode</h5>

            <div class="card border-0 shadow-sm">
                <div class="card-body">

                    {{-- FORM TAMBAH --}}
                    <form method="POST" action="{{ route('admin.display-schedule.store') }}">
                        @csrf
                        <input type="hidden" name="display_config_id" value="{{ $displayConfig->id }}">

                        <div class="row g-2">
                            <div class="col-md-3">
                                <input type="time" name="start_time" class="form-control" required>
                            </div>

                            <div class="col-md-3">
                                <input type="time" name="end_time" class="form-control" required>
                            </div>

                            <div class="col-md-3">
                                <select name="mode" class="form-control">
                                    <option value="dashboard">Dashboard</option>
                                    <option value="announcement">Announcement</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <button class="btn btn-primary w-100">
                                    <i class="fas fa-plus"></i> Tambah
                                </button>
                            </div>
                        </div>
                    </form>

                    {{-- LIST SCHEDULE --}}
                    @if($displayConfig->schedules && $displayConfig->schedules->count())
                        <div class="mt-4">
                            <h6 class="fw-bold">Daftar Schedule</h6>

                            <table class="table table-bordered align-middle">
                                <thead>
                                    <tr>
                                        <th>Jam</th>
                                        <th>Mode</th>
                                        <th width="100">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($displayConfig->schedules as $schedule)
                                        <tr>
                                            <td>
                                                {{ $schedule->start_time }} - {{ $schedule->end_time }}
                                            </td>

                                            <td>
                                                <span class="badge bg-{{ $schedule->mode == 'announcement' ? 'danger' : 'success' }}">
                                                    {{ $schedule->mode }}
                                                </span>
                                            </td>

                                            <td>
                                                <form method="POST" action="{{ route('admin.display-schedule.destroy', $schedule->id) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-danger btn-sm" onclick="return confirm('Hapus schedule?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                </div>
            </div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
    const el = document.getElementById('sortable-content');

    if (el) {
        new Sortable(el, {
            animation: 150,
            onEnd: function () {
                let order = [];

                document.querySelectorAll('#sortable-content tr').forEach((row) => {
                    order.push(row.dataset.id);
                });

                fetch("{{ route('admin.display-content.reorder') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({ order: order })
                });
            }
        });
    }
</script>
</div>
@endsection