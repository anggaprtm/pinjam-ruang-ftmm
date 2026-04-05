@extends('layouts.admin')

@section('content')
<div class="content">

    <h3 class="mb-4 fw-bold">Tambah Signage Config</h3>

    <div class="card shadow-sm border-0">
        <div class="card-body">

            <form method="POST" action="{{ route('admin.display-config.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-bold">Lokasi / Device TV</label>
                    <input type="text" name="location" class="form-control" placeholder="Contoh: default, lantai6, lantai7, dekanat" required>
                    <small class="text-muted">Gunakan <b>default</b> untuk dashboard utama (Sarpras).</small>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Mode</label>
                    <select name="mode" class="form-control">
                        <option value="dashboard">Dashboard</option>
                        <option value="announcement">Announcement</option>
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
                <div class="mb-4 p-3 border rounded bg-light">
                    <label class="form-label fw-bold text-primary mb-3">
                        <i class="fas fa-tv me-2"></i>Pengaturan Visibilitas Panel (Khusus Mode Dashboard)
                    </label>
                    <div class="row g-3">
                        @php
                            $panels = [
                                'lectures' => 'Agenda Perkuliahan',
                                'events' => 'Agenda Kegiatan',
                                'meetings' => 'Sidang & Rapat',
                                'agenda' => 'Agenda Fakultas',
                                'pending_requests' => 'Permintaan Pending (Utama)',
                                'cars' => 'Status Mobil (Utama)',
                            ];
                        @endphp
                        
                        @foreach($panels as $key => $label)
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    {{-- Trik hidden input agar unchecked tetap terkirim sebagai 0 --}}
                                    <input type="hidden" name="panel_visibility[{{ $key }}]" value="0">
                                    <input class="form-check-input cursor-pointer" type="checkbox" name="panel_visibility[{{ $key }}]" value="1" id="panel_{{ $key }}" checked>
                                    <label class="form-check-label cursor-pointer" for="panel_{{ $key }}">{{ $label }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Content Type</label>
                    <select name="content_type" class="form-control">
                        <option value="image">Image</option>
                        <option value="text">Text</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Content Value (optional)</label>
                    <textarea name="content_value" class="form-control"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Upload Image</label>
                    <input type="file" name="image" class="form-control">
                </div>

                <button class="btn btn-primary shadow-sm">
                    <i class="fas fa-save me-1"></i> Simpan
                </button>

            </form>

        </div>
    </div>

</div>
@endsection