@extends('layouts.admin')
@section('content')

<div class="card form-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">
            <i class="fas fa-plus-circle me-2 text-success"></i> Tambah Agenda Fakultas
        </h4>
        <a href="{{ route('admin.agenda-fakultas.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('admin.agenda-fakultas.store') }}">
            @csrf

            {{-- JUDUL --}}
            <div class="form-group mb-3">
                <label class="form-label required" for="judul">Judul Agenda</label>
                <input class="form-control {{ $errors->has('judul') ? 'is-invalid' : '' }}"
                       type="text" name="judul" id="judul"
                       value="{{ old('judul') }}"
                       placeholder="Contoh: Wisuda Semester Gasal 2025/2026"
                       required>
                @if($errors->has('judul'))
                    <div class="invalid-feedback">{{ $errors->first('judul') }}</div>
                @endif
            </div>

            {{-- DESKRIPSI --}}
            <div class="form-group mb-3">
                <label class="form-label" for="deskripsi">Deskripsi <span class="text-muted">(opsional)</span></label>
                <textarea class="form-control {{ $errors->has('deskripsi') ? 'is-invalid' : '' }}"
                          name="deskripsi" id="deskripsi" rows="3"
                          placeholder="Keterangan singkat tentang agenda ini...">{{ old('deskripsi') }}</textarea>
                @if($errors->has('deskripsi'))
                    <div class="invalid-feedback">{{ $errors->first('deskripsi') }}</div>
                @endif
            </div>

            {{-- KATEGORI & WARNA --}}
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label required" for="kategori">Kategori</label>
                        <select class="form-control {{ $errors->has('kategori') ? 'is-invalid' : '' }}"
                                name="kategori" id="kategori" required>
                            @foreach($kategoriOptions as $opt)
                                <option value="{{ $opt }}" {{ old('kategori') === $opt ? 'selected' : '' }}>
                                    {{ $opt }}
                                </option>
                            @endforeach
                        </select>
                        @if($errors->has('kategori'))
                            <div class="invalid-feedback">{{ $errors->first('kategori') }}</div>
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label required" for="warna">Warna Aksen</label>
                        <select class="form-control {{ $errors->has('warna') ? 'is-invalid' : '' }}"
                                name="warna" id="warna" required>
                            @foreach($warnaOptions as $hex => $label)
                                <option value="{{ $hex }}" {{ old('warna', '#2dd4bf') === $hex ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @if($errors->has('warna'))
                            <div class="invalid-feedback">{{ $errors->first('warna') }}</div>
                        @endif
                        {{-- Preview warna --}}
                        <div class="mt-2 d-flex align-items-center gap-2">
                            <span id="warnaPreview"
                                  style="display:inline-block;width:20px;height:20px;border-radius:4px;background:{{ old('warna','#2dd4bf') }};border:1px solid rgba(0,0,0,0.15);">
                            </span>
                            <small class="text-muted">Preview warna aksen di signage</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TANGGAL --}}
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label required" for="tanggal_mulai">Tanggal Mulai</label>
                        <input class="form-control {{ $errors->has('tanggal_mulai') ? 'is-invalid' : '' }}"
                               type="date" name="tanggal_mulai" id="tanggal_mulai"
                               value="{{ old('tanggal_mulai') }}" required>
                        @if($errors->has('tanggal_mulai'))
                            <div class="invalid-feedback">{{ $errors->first('tanggal_mulai') }}</div>
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="tanggal_selesai">
                            Tanggal Selesai <span class="text-muted">(opsional, jika multi-hari)</span>
                        </label>
                        <input class="form-control {{ $errors->has('tanggal_selesai') ? 'is-invalid' : '' }}"
                               type="date" name="tanggal_selesai" id="tanggal_selesai"
                               value="{{ old('tanggal_selesai') }}">
                        @if($errors->has('tanggal_selesai'))
                            <div class="invalid-feedback">{{ $errors->first('tanggal_selesai') }}</div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- SEHARIAN / WAKTU --}}
            <div class="form-group mb-3">
                <div class="form-check">
                    <input type="hidden" name="is_all_day" value="0">
                    <input class="form-check-input" type="checkbox"
                           name="is_all_day" id="is_all_day" value="1"
                           {{ old('is_all_day', '1') === '1' ? 'checked' : '' }}
                           onchange="toggleWaktu(this)">
                    <label class="form-check-label" for="is_all_day">
                        <i class="fas fa-sun me-1 text-warning"></i> Seharian penuh (tanpa jam spesifik)
                    </label>
                </div>
            </div>

            {{-- WAKTU (tersembunyi jika seharian) --}}
            <div class="row mb-3" id="waktuRow" style="{{ old('is_all_day', '1') === '1' ? 'display:none;' : '' }}">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="waktu_mulai">Jam Mulai</label>
                        <input class="form-control {{ $errors->has('waktu_mulai') ? 'is-invalid' : '' }}"
                               type="time" name="waktu_mulai" id="waktu_mulai"
                               value="{{ old('waktu_mulai') }}">
                        @if($errors->has('waktu_mulai'))
                            <div class="invalid-feedback">{{ $errors->first('waktu_mulai') }}</div>
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="waktu_selesai">Jam Selesai</label>
                        <input class="form-control {{ $errors->has('waktu_selesai') ? 'is-invalid' : '' }}"
                               type="time" name="waktu_selesai" id="waktu_selesai"
                               value="{{ old('waktu_selesai') }}">
                        @if($errors->has('waktu_selesai'))
                            <div class="invalid-feedback">{{ $errors->first('waktu_selesai') }}</div>
                        @endif
                    </div>
                </div>
            </div>

            <hr class="my-4">

            {{-- TAMPILAN SIGNAGE --}}
            <h6 class="mb-3 text-muted text-uppercase fw-bold" style="font-size:0.75rem;letter-spacing:0.08em;">
                <i class="fas fa-tv me-2"></i> Pengaturan Signage
            </h6>

            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-check">
                        <input type="hidden" name="tampil_di_signage" value="0">
                        <input class="form-check-input" type="checkbox"
                               name="tampil_di_signage" id="tampil_di_signage" value="1"
                               {{ old('tampil_di_signage', '1') === '1' ? 'checked' : '' }}>
                        <label class="form-check-label" for="tampil_di_signage">
                            <i class="fas fa-tv me-1 text-primary"></i>
                            Tampilkan di Signage TV
                        </label>
                        <div class="form-text text-muted">Agenda akan muncul di panel "Agenda Fakultas" pada layar signage.</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check">
                        <input type="hidden" name="tampil_countdown" value="0">
                        <input class="form-check-input" type="checkbox"
                               name="tampil_countdown" id="tampil_countdown" value="1"
                               {{ old('tampil_countdown') === '1' ? 'checked' : '' }}>
                        <label class="form-check-label" for="tampil_countdown">
                            <i class="fas fa-stopwatch me-1 text-warning"></i>
                            Tampilkan Countdown
                        </label>
                        <div class="form-text text-muted">Hitung mundur "X hari lagi" akan muncul di bagian atas panel signage.</div>
                    </div>
                </div>
            </div>

            {{-- URUTAN --}}
            <div class="form-group mb-4">
                <label class="form-label" for="urutan">Urutan Tampil <span class="text-muted">(angka lebih kecil = lebih atas)</span></label>
                <input class="form-control {{ $errors->has('urutan') ? 'is-invalid' : '' }}"
                       type="number" name="urutan" id="urutan"
                       value="{{ old('urutan', 0) }}" min="0" style="max-width:150px;">
                @if($errors->has('urutan'))
                    <div class="invalid-feedback">{{ $errors->first('urutan') }}</div>
                @endif
            </div>

            {{-- TOMBOL --}}
            <div class="card-footer text-end px-0 pb-0 bg-transparent border-0">
                <a href="{{ route('admin.agenda-fakultas.index') }}" class="btn btn-secondary me-2">
                    <i class="fas fa-times me-1"></i> Batal
                </a>
                <button class="btn btn-success" type="submit">
                    <i class="fas fa-save me-1"></i> Simpan Agenda
                </button>
            </div>

        </form>
    </div>
</div>

@endsection

@section('scripts')
@parent
<script>
function toggleWaktu(checkbox) {
    document.getElementById('waktuRow').style.display = checkbox.checked ? 'none' : '';
}

// Preview warna aksen
document.getElementById('warna').addEventListener('change', function () {
    document.getElementById('warnaPreview').style.background = this.value;
});
</script>
@endsection