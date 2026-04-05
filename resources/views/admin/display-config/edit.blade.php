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

            {{-- ================= CONFIG ================= --}}
            <form method="POST" action="{{ route('admin.display-config.update', $displayConfig->id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label fw-bold">Lokasi / Device TV</label>
                    <input type="text" name="location" class="form-control"
                        value="{{ old('location', $displayConfig->location) }}" required>
                    <small class="text-muted">Gunakan <b>default</b> untuk dashboard utama (Sarpras).</small>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Mode</label>
                    <select name="mode" id="modeSelect" class="form-control">
                        <option value="dashboard" {{ $displayConfig->mode == 'dashboard' ? 'selected' : '' }}>Dashboard</option>
                        <option value="announcement" {{ $displayConfig->mode == 'announcement' ? 'selected' : '' }}>Announcement</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Running Text (News Ticker)</label>
                    <input type="text" name="running_text" class="form-control" 
                           placeholder="Contoh: Info: Jadwal ujian UTS dimajukan..." 
                           value="{{ old('running_text', $displayConfig->running_text ?? '') }}">
                    <small class="text-muted">Kosongkan jika tidak ingin menampilkan teks berjalan di TV ini.</small>
                </div>

                {{-- 🔥 PANEL VISIBILITY CONFIG (NEW) --}}
                <div class="mb-4 p-3 border rounded bg-light" id="panelVisibilitySection">
                    <label class="form-label fw-bold text-primary mb-3">
                        <i class="fas fa-tv me-2"></i>Pengaturan Visibilitas Panel
                    </label>
                    <div class="row g-3">
                        @php
                            // Ambil data JSON dari database (sudah di-cast jadi array di Model)
                            $vis = is_array($displayConfig->panel_visibility) ? $displayConfig->panel_visibility : [];
                            
                            $panels = [
                                'lectures' => 'Agenda Perkuliahan (Kiri)',
                                'events' => 'Agenda Kegiatan (Tengah)',
                                'meetings' => 'Sidang & Rapat (Kanan)',
                                'agenda' => 'Agenda Fakultas (Tengah)',
                                'rooms' => 'Ketersediaan Ruang (Tengah)',
                                'pending_requests' => 'Permintaan Pending (Kiri)',
                                'cars' => 'Status Mobil (Kanan)',
                            ];
                        @endphp
                        
                        @foreach($panels as $key => $label)
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="panel_visibility[{{ $key }}]" value="0">
                                    {{-- Cek jika disetting true, ATAU jika belum pernah disetting sama sekali (default true) --}}
                                    <input class="form-check-input cursor-pointer" type="checkbox" name="panel_visibility[{{ $key }}]" value="1" id="edit_panel_{{ $key }}" 
                                        {{ (isset($vis[$key]) && $vis[$key]) || !isset($vis[$key]) ? 'checked' : '' }}>
                                    <label class="form-check-label cursor-pointer" for="edit_panel_{{ $key }}">{{ $label }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div id="contentSection">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Content Type</label>
                        <select name="content_type" id="typeSelect" class="form-control">
                            <option value="image" {{ $displayConfig->content_type == 'image' ? 'selected' : '' }}>Image</option>
                            <option value="text" {{ $displayConfig->content_type == 'text' ? 'selected' : '' }}>Text</option>
                            <option value="video" {{ $displayConfig->content_type == 'video' ? 'selected' : '' }}>Video</option>
                        </select>
                    </div>

                    <div class="mb-3" id="textSection">
                        <label class="form-label fw-bold">Content Value</label>
                        <textarea name="content_value" class="form-control">{{ old('content_value', $displayConfig->content_value) }}</textarea>
                    </div>

                    <div class="mb-3" id="uploadSection">
                        <label class="form-label fw-bold">Upload File</label>
                        <input type="file" name="image" class="form-control">
                    </div>

                    {{-- PREVIEW --}}
                    @if($displayConfig->image_path)
                        <div class="mb-3 text-center">
                            @if(str_contains($displayConfig->image_path, '.mp4'))
                                <video src="{{ asset('storage/'.$displayConfig->image_path) }}" style="max-height:200px;" controls></video>
                            @else
                                <img src="{{ asset('storage/'.$displayConfig->image_path) }}" style="max-height:200px;">
                            @endif
                        </div>
                    @endif

                </div>

                <button class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Update
                </button>
            </form>

            {{-- ================= SLIDESHOW ================= --}}
            <hr class="my-4">

            <h5 class="fw-bold">Slide Content</h5>

            <div class="card border-0 shadow-sm">
                <div class="card-body">

                    {{-- FORM TAMBAH --}}
                    <form method="POST" action="{{ route('admin.display-content.store') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="display_config_id" value="{{ $displayConfig->id }}">

                        <div class="row g-2">
                            <div class="col-md-2">
                                <select name="type" class="form-control">
                                    <option value="image">Image</option>
                                    <option value="text">Text</option>
                                    <option value="video">Video</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <input type="file" name="image" class="form-control">
                            </div>

                            <div class="col-md-3">
                                <input type="text" name="value" class="form-control" placeholder="Text / URL">
                            </div>

                            <div class="col-md-2">
                                <input type="number" name="duration" class="form-control" value="5">
                            </div>

                            <div class="col-md-2">
                                <button class="btn btn-success w-100">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </form>

                    {{-- LIST --}}
                    @if($displayConfig->contents && $displayConfig->contents->count())
                        <div class="mt-4">
                            <table class="table table-bordered align-middle">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Preview</th>
                                        <th>Durasi</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="sortable-content">
                                    @foreach($displayConfig->contents as $item)
                                        <tr data-id="{{ $item->id }}" style="cursor: grab;">
                                            <td>{{ $loop->iteration }}</td>

                                            <td>
                                                @if($item->type === 'image')
                                                    <img src="{{ asset('storage/'.$item->image_path) }}" height="60">
                                                @elseif($item->type === 'video')
                                                    <video src="{{ asset('storage/'.$item->image_path) }}" height="60"></video>
                                                @else
                                                    {{ $item->value }}
                                                @endif
                                            </td>

                                            <td>{{ $item->duration }}s</td>

                                            <td>
                                                <form method="POST" action="{{ route('admin.display-content.destroy', $item->id) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-danger btn-sm">
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

            {{-- ================= SCHEDULE ================= --}}
            <hr class="my-4">

            <h5 class="fw-bold">Schedule Mode</h5>

            <div class="card border-0 shadow-sm">
                <div class="card-body">

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
                                <button class="btn btn-primary w-100">Tambah</button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>

</div>

{{-- ================= JS ================= --}}
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // 🔥 SORTABLE
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
                    body: JSON.stringify({ order })
                });
            }
        });
    }

    // 🔥 UX TOGGLE
    function toggleForm() {
        const mode = document.getElementById('modeSelect').value;
        const type = document.getElementById('typeSelect').value;

        const contentSection = document.getElementById('contentSection');
        const uploadSection = document.getElementById('uploadSection');

        if (mode === 'dashboard') {
            contentSection.style.display = 'none';
            return;
        }

        contentSection.style.display = 'block';
        uploadSection.style.display = (type === 'text') ? 'none' : 'block';
    }

    toggleForm();

    document.getElementById('modeSelect').addEventListener('change', toggleForm);
    document.getElementById('typeSelect').addEventListener('change', toggleForm);
});
</script>

@endsection