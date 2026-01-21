@extends('layouts.admin')
@section('content')

<div class="card form-card">
    <div class="card-header">
        <h4 class="mb-0">{{ trans('global.edit') }} {{ trans('cruds.kegiatan.title_singular') }}</h4>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.kegiatan.update", [$kegiatan->id]) }}" enctype="multipart/form-data">
            @method('PUT')
            @csrf
            <div class="row">
                {{-- Kolom Kiri --}}
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label required" for="jenis_kegiatan">Jenis Kegiatan</label>
                        <select class="form-control select2 {{ $errors->has('jenis_kegiatan') ? 'is-invalid' : '' }}" name="jenis_kegiatan" id="jenis_kegiatan" required>
                            @foreach(['Seminar Proposal', 'Sidang Skripsi', 'Rapat', 'Himpunan', 'Lomba', 'Lainnya'] as $jenis)
                                <option value="{{ $jenis }}" {{ (old('jenis_kegiatan') ? old('jenis_kegiatan') : $kegiatan->jenis_kegiatan) == $jenis ? 'selected' : '' }}>{{ $jenis }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="form-dosen-container" style="display: none;" class="p-3 mb-3 bg-light border rounded">
                        <h6 class="fw-bold mb-3 text-primary"><i class="fas fa-user-graduate me-2"></i>Detail Dosen</h6>
                        
                        <div class="row">
                            {{-- Pembimbing selalu 2 --}}
                            <div class="col-md-6 mb-2">
                                <label class="form-label" for="dosen_pembimbing_1">Dosen Pembimbing 1</label>
                                <input class="form-control" type="text" name="dosen_pembimbing_1" id="dosen_pembimbing_1" value="{{ old('dosen_pembimbing_1', $kegiatan->dosen_pembimbing_1) }}">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label" for="dosen_pembimbing_2">Dosen Pembimbing 2</label>
                                <input class="form-control" type="text" name="dosen_pembimbing_2" id="dosen_pembimbing_2" value="{{ old('dosen_pembimbing_2', $kegiatan->dosen_pembimbing_2) }}">
                            </div>
                            
                            {{-- Penguji 1 --}}
                            <div class="col-md-6 mb-2">
                                <label class="form-label" for="dosen_penguji_1">Dosen Penguji 1</label>
                                <input class="form-control" type="text" name="dosen_penguji_1" id="dosen_penguji_1" value="{{ old('dosen_penguji_1', $kegiatan->dosen_penguji_1) }}">
                            </div>

                            {{-- Penguji 2 (Hanya untuk Sidang Skripsi) --}}
                            <div class="col-md-6 mb-2" id="container-penguji-2">
                                <label class="form-label" for="dosen_penguji_2">Dosen Penguji 2</label>
                                <input class="form-control" type="text" name="dosen_penguji_2" id="dosen_penguji_2" value="{{ old('dosen_penguji_2', $kegiatan->dosen_penguji_2) }}">
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label required" for="nama_kegiatan">{{ trans('cruds.kegiatan.fields.nama_kegiatan') }}</label>
                        <input class="form-control {{ $errors->has('nama_kegiatan') ? 'is-invalid' : '' }}" type="text" name="nama_kegiatan" id="nama_kegiatan" value="{{ old('nama_kegiatan', $kegiatan->nama_kegiatan) }}" required>
                        @if($errors->has('nama_kegiatan'))
                            <div class="invalid-feedback">{{ $errors->first('nama_kegiatan') }}</div>
                        @endif
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label" for="ruangan_id">{{ trans('cruds.kegiatan.fields.ruangan') }}</label>
                        <select class="form-control select2 {{ $errors->has('ruangan_id') ? 'is-invalid' : '' }}" name="ruangan_id" id="ruangan_id">
                            @foreach($ruangan as $id => $entry)
                                <option value="{{ $id }}" {{ (old('ruangan_id') ? old('ruangan_id') : $kegiatan->ruangan->id ?? '') == $id ? 'selected' : '' }}>{{ $entry }}</option>
                            @endforeach
                        </select>
                        @if($errors->has('ruangan_id'))
                            <div class="invalid-feedback">{{ $errors->first('ruangan_id') }}</div>
                        @endif
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label" for="nama_pic">Nama PIC</label>
                        <input class="form-control {{ $errors->has('nama_pic') ? 'is-invalid' : '' }}" type="text" name="nama_pic" value="{{ old('nama_pic', $kegiatan->nama_pic) }}">
                        @if($errors->has('nama_pic'))
                            <div class="invalid-feedback">{{ $errors->first('nama_pic') }}</div>
                        @endif
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label" for="nomor_telepon">Nomor Telepon PIC</label>
                        <input class="form-control {{ $errors->has('nomor_telepon') ? 'is-invalid' : '' }}" type="text" name="nomor_telepon" value="{{ old('nomor_telepon', $kegiatan->nomor_telepon) }}">
                        @if($errors->has('nomor_telepon'))
                            <div class="invalid-feedback">{{ $errors->first('nomor_telepon') }}</div>
                        @endif
                    </div>
                </div>

                {{-- Kolom Kanan --}}
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label required" for="waktu_mulai">{{ trans('cruds.kegiatan.fields.waktu_mulai') }}</label>
                        <div class="input-group">
                            <span class="input-group-text" id="waktu_mulai_toggle" role="button" data-bs-toggle="tooltip" title="Buka picker (Waktu Mulai)" aria-label="Buka picker waktu mulai"><i class="fas fa-calendar-alt"></i></span>
                            <input class="form-control datetime {{ $errors->has('waktu_mulai') ? 'is-invalid' : '' }}" type="text" name="waktu_mulai" id="waktu_mulai" value="{{ old('waktu_mulai', $kegiatan->waktu_mulai) }}" required>
                        </div>
                        @if($errors->has('waktu_mulai'))
                            <div class="invalid-feedback">{{ $errors->first('waktu_mulai') }}</div>
                        @endif
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label required" for="waktu_selesai">{{ trans('cruds.kegiatan.fields.waktu_selesai') }}</label>
                        <div class="input-group">
                            <span class="input-group-text" id="waktu_selesai_toggle" role="button" data-bs-toggle="tooltip" title="Buka picker (Waktu Selesai)" aria-label="Buka picker waktu selesai"><i class="fas fa-calendar-alt"></i></span>
                            <input class="form-control datetime {{ $errors->has('waktu_selesai') ? 'is-invalid' : '' }}" type="text" name="waktu_selesai" id="waktu_selesai" value="{{ old('waktu_selesai', $kegiatan->waktu_selesai) }}" required>
                        </div>
                        @if($errors->has('waktu_selesai'))
                            <div class="invalid-feedback">{{ $errors->first('waktu_selesai') }}</div>
                        @endif
                    </div>
                </div>
            </div>

            @if(auth()->user()->isAdmin())
                <div class="form-group mb-3">
                    <label for="deskripsi">Keterangan</label>
                    <textarea class="form-control {{ $errors->has('deskripsi') ? 'is-invalid' : '' }}" name="deskripsi" id="deskripsi">{{ old('deskripsi', $kegiatan->deskripsi) }}</textarea>
                    @if($errors->has('deskripsi'))
                        <div class="invalid-feedback">{{ $errors->first('deskripsi') }}</div>
                    @endif
                </div>

                <div class="form-group mb-3">
                    <label class="required" for="user_id">{{ trans('cruds.kegiatan.fields.user') }}</label>
                    <select class="form-control select2 {{ $errors->has('user_id') ? 'is-invalid' : '' }}" name="user_id" id="user_id" required>
                        @foreach($users as $id => $entry)
                            <option value="{{ $id }}" {{ (old('user_id') ? old('user_id') : $kegiatan->user->id ?? '') == $id ? 'selected' : '' }}>{{ $entry }}</option>
                        @endforeach
                    </select>
                    @if($errors->has('user_id'))
                        <div class="invalid-feedback">{{ $errors->first('user_id') }}</div>
                    @endif
                </div>
            @endif

            {{-- ===================== SURAT IZIN ===================== --}}
            <div class="form-group mb-3">
                <label for="surat_izin" class="form-label fw-bold">Surat Izin (PDF):</label>

                {{-- Link surat izin lama --}}
                @if ($kegiatan->surat_izin)
                    <div class="mb-2">
                        <a href="{{ asset('storage/' . $kegiatan->surat_izin) }}" target="_blank">
                            Lihat surat izin saat ini
                        </a>
                        <div class="small text-muted">Upload baru untuk mengganti</div>
                    </div>
                @endif

                <div class="input-group">
                    {{-- Hidden file input --}}
                    <input
                        class="d-none {{ $errors->has('surat_izin') ? 'is-invalid' : '' }}"
                        type="file"
                        name="surat_izin"
                        id="surat_izin"
                        accept="application/pdf"
                    >

                    {{-- Custom button --}}
                    <label for="surat_izin" class="btn btn-outline-dark mb-0 rounded-end-0">
                        <i class="fas fa-upload me-2"></i>Pilih File
                    </label>

                    {{-- File name display --}}
                    <span class="form-control rounded-start-0" id="surat-izin-display">
                        Tidak ada file yang dipilih
                    </span>
                </div>

                {{-- Error message --}}
                @if($errors->has('surat_izin'))
                    <div class="invalid-feedback d-block">{{ $errors->first('surat_izin') }}</div>
                @endif

                <small class="form-text text-muted">
                    Surat izin harus berupa PDF ukuran maksimal 2MB.
                </small>
            </div>


            {{-- ===================== POSTER ===================== --}}
            <div class="form-group mb-3">
                <label for="poster" class="form-label fw-bold">Poster Kegiatan (Opsional):</label>

                {{-- Preview Poster Lama --}}
                @if($kegiatan->poster)
                    <div class="mb-2">
                        <img src="{{ asset('storage/' . $kegiatan->poster) }}"
                            alt="Poster Saat Ini"
                            class="img-thumbnail"
                            style="max-height: 150px;">
                        <div class="small text-muted mt-1">Poster saat ini (Upload baru untuk mengganti)</div>
                    </div>
                @endif

                <div class="input-group">
                    {{-- Hidden file input --}}
                    <input
                        class="d-none {{ $errors->has('poster') ? 'is-invalid' : '' }}"
                        type="file"
                        name="poster"
                        id="poster"
                        accept="image/*"
                    >

                    {{-- Custom button --}}
                    <label for="poster" class="btn btn-outline-dark mb-0 rounded-end-0">
                        <i class="fas fa-upload me-2"></i>Pilih Gambar
                    </label>

                    {{-- File name display --}}
                    <span class="form-control rounded-start-0" id="poster-display">
                        Tidak ada file yang dipilih
                    </span>
                </div>

                {{-- Error message --}}
                @if($errors->has('poster'))
                    <div class="invalid-feedback d-block">{{ $errors->first('poster') }}</div>
                @endif

                <small class="form-text text-muted">
                    Format: JPG, PNG. Maks: 2MB.
                </small>
            </div>

            <div class="card-footer text-end">
                <a href="{{ route('admin.kegiatan.index') }}" class="btn btn-secondary me-2">Batal</a>
                <button class="btn btn-primary" type="submit">
                    {{ trans('global.save') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
@parent
<script>
    $(document).ready(function() {
        // Toggle datetimepicker when calendar icon clicked (edit form)
        $('#waktu_mulai_toggle').on('click', function(e) {
            e.preventDefault();
            try {
                $('#waktu_mulai').data('DateTimePicker').show();
            } catch (err) {
                $('#waktu_mulai').focus();
            }
        });

        $('#waktu_selesai_toggle').on('click', function(e) {
            e.preventDefault();
            try {
                $('#waktu_selesai').data('DateTimePicker').show();
            } catch (err) {
                $('#waktu_selesai').focus();
            }
        });
    });
</script>
<script>
    $(document).ready(function() {
        // Fungsi untuk cek jenis kegiatan
        function checkJenisKegiatan() {
            var jenis = $('#jenis_kegiatan').val();
            var container = $('#form-dosen-container');
            var penguji2 = $('#container-penguji-2');

            // Reset dulu visibility
            container.hide();
            penguji2.hide();

            if (jenis === 'Seminar Proposal') {
                container.slideDown();
                // Sempro: 2 Pembimbing, 1 Penguji (Penguji 2 hide)
                penguji2.hide(); 
            } 
            else if (jenis === 'Sidang Skripsi') {
                container.slideDown();
                // Sidang: 2 Pembimbing, 2 Penguji (Penguji 2 show)
                penguji2.show();
            }
        }

        // Jalankan saat halaman load (siapa tahu ada error validasi dan form balik)
        checkJenisKegiatan();

        // Jalankan saat user ganti pilihan dropdown
        $('#jenis_kegiatan').change(function() {
            checkJenisKegiatan();
        });
    });
</script>
<script>
document.getElementById('surat_izin').addEventListener('change', function () {
    const fileName = this.files.length ? this.files[0].name : "Tidak ada file yang dipilih";
    document.getElementById('surat-izin-display').textContent = fileName;
});

document.getElementById('poster').addEventListener('change', function () {
    const fileName = this.files.length ? this.files[0].name : "Tidak ada file yang dipilih";
    document.getElementById('poster-display').textContent = fileName;
});
</script>
@endsection
