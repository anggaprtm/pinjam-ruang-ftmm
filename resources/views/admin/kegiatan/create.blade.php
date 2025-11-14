@extends('layouts.admin')
@section('content')

<div class="card form-card">
    <div class="card-header">
        <h4 class="mb-0">{{ trans('global.create') }} {{ trans('cruds.kegiatan.title_singular') }}</h4>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.kegiatan.store") }}" enctype="multipart/form-data">
            @csrf
            <div class="row">
                {{-- Kolom Kiri --}}
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label required" for="nama_kegiatan">{{ trans('cruds.kegiatan.fields.nama_kegiatan') }}</label>
                        <input class="form-control {{ $errors->has('nama_kegiatan') ? 'is-invalid' : '' }}" type="text" name="nama_kegiatan" id="nama_kegiatan" value="{{ old('nama_kegiatan', '') }}" required>
                        @if($errors->has('nama_kegiatan'))
                            <div class="invalid-feedback">{{ $errors->first('nama_kegiatan') }}</div>
                        @endif
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label required" for="ruangan_id">{{ trans('cruds.kegiatan.fields.ruangan') }}</label>
                        <select class="form-control select2 {{ $errors->has('ruangan') ? 'is-invalid' : '' }}" name="ruangan_id" id="ruangan_id" required oninvalid="this.setCustomValidity('Silakan pilih ruangan terlebih dahulu')">
                            <option value=""></option>
                            @foreach($ruangan as $id => $entry)
                                <option value="{{ $id }}" {{ old('ruangan_id') == $id ? 'selected' : '' }}>{{ $entry }}</option>
                            @endforeach
                        </select>
                        @if($errors->has('ruangan'))
                            <div class="invalid-feedback">{{ $errors->first('ruangan') }}</div>
                        @endif
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label required" for="nama_pic">Nama PIC</label>
                        <input class="form-control {{ $errors->has('nama_pic') ? 'is-invalid' : '' }}" type="text" name="nama_pic" id="nama_pic" value="{{ old('nama_pic', '') }}" required>
                        @if($errors->has('nama_pic'))
                            <div class="invalid-feedback">{{ $errors->first('nama_pic') }}</div>
                        @endif
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label required" for="nomor_telepon">Nomor Telepon PIC</label>
                        <input class="form-control {{ $errors->has('nomor_telepon') ? 'is-invalid' : '' }}" type="text" name="nomor_telepon" id="nomor_telepon" value="{{ old('nomor_telepon', '') }}" required minlength="9" maxlength="15" inputmode="numeric" pattern="^0[0-9]+$">
                        @if($errors->has('nomor_telepon'))
                            <div class="invalid-feedback">{{ $errors->first('nomor_telepon') }}</div>
                        @endif
                        {{-- Client-side feedback placeholder --}}
                        <div id="nomor-telepon-client-error" class="invalid-feedback d-none"></div>
                    </div>
                </div>

                {{-- Kolom Kanan --}}
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label required" for="waktu_mulai">{{ trans('cruds.kegiatan.fields.waktu_mulai') }}</label>
                        <div class="input-group">
                            <span class="input-group-text" id="waktu_mulai_toggle" role="button" data-bs-toggle="tooltip" title="Buka picker (Waktu Mulai)" aria-label="Buka picker waktu mulai"><i class="fas fa-calendar-alt"></i></span>
                            <input class="form-control datetime {{ $errors->has('waktu_mulai') ? 'is-invalid' : '' }}" type="text" name="waktu_mulai" id="waktu_mulai" value="{{ old('waktu_mulai') }}" required>
                        </div>
                        @if($errors->has('waktu_mulai'))
                            <div class="invalid-feedback">{{ $errors->first('waktu_mulai') }}</div>
                        @endif
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label required" for="waktu_selesai">{{ trans('cruds.kegiatan.fields.waktu_selesai') }}</label>
                        <div class="input-group">
                            <span class="input-group-text" id="waktu_selesai_toggle" role="button" data-bs-toggle="tooltip" title="Buka picker (Waktu Selesai)" aria-label="Buka picker waktu selesai"><i class="fas fa-calendar-alt"></i></span>
                            <input class="form-control datetime {{ $errors->has('waktu_selesai') ? 'is-invalid' : '' }}" type="text" name="waktu_selesai" id="waktu_selesai" value="{{ old('waktu_selesai') }}" required>
                        </div>
                        @if($errors->has('waktu_selesai'))
                            <div class="invalid-feedback">{{ $errors->first('waktu_selesai') }}</div>
                        @endif
                    </div>

                    @if(auth()->user()->isAdmin())
                        <div class="form-group mb-3">
                            <label class="required" for="user_id">Peminjam</label>
                            <select class="form-control select2 {{ $errors->has('user') ? 'is-invalid' : '' }}" name="user_id" id="user_id" required>
                                {{-- Menambahkan opsi 'pleaseSelect' sesuai referensi --}}
                                <option value="">{{ trans('global.pleaseSelect' )}}</option>
                                @foreach($users as $id => $entry)
                                    <option value="{{ $id }}" {{ old('user_id') == $id ? 'selected' : '' }}>{{ $entry }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('user'))
                                <div class="invalid-feedback">{{ $errors->first('user') }}</div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <div class="form-group mb-3">
                <label class="form-label" for="deskripsi">{{ trans('cruds.kegiatan.fields.deskripsi') }}</label>
                <textarea class="form-control {{ $errors->has('deskripsi') ? 'is-invalid' : '' }}" name="deskripsi" id="deskripsi">{{ old('deskripsi') }}</textarea>
                @if($errors->has('deskripsi'))
                    <div class="invalid-feedback">{{ $errors->first('deskripsi') }}</div>
                @endif
            </div>

            {{-- =================== TOGGLE UNTUK KEGIATAN BERULANG =================== --}}
            <div class="form-group mb-3">
                <div class="d-flex align-items-center">
                    <label for="toggle-recurring" class="me-3 mb-0 fw-semibold">
                        Jadikan kegiatan berulang?
                    </label>
                    <label class="toggle-switch mb-0">
                        <input type="checkbox" id="toggle-recurring" name="is_recurring" {{ old('is_recurring') ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
                <small class="form-text text-muted">
                    Aktifkan jika kegiatan ini akan berlangsung secara berulang (mingguan/bulanan).
                </small>
            </div>

            {{-- =================== BAGIAN BERULANG YANG BISA DISEMBUNYIKAN =================== --}}
            {{-- Kita bungkus bagian ini dengan div agar mudah ditarget oleh JavaScript --}}
            <div id="recurring-options" style="display: none;">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="berulang_sampai">Berulang sampai</label>
                            <input class="form-control date {{ $errors->has('berulang_sampai') ? 'is-invalid' : '' }}" type="text" name="berulang_sampai" id="berulang_sampai" value="{{ old('berulang_sampai') }}">
                            @if($errors->has('berulang_sampai'))
                                <div class="invalid-feedback">{{ $errors->first('berulang_sampai') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="tipe_berulang">Tipe Pengulangan</label>
                            <select class="form-control" name="tipe_berulang" id="tipe_berulang">
                                <option value="harian" {{ old('tipe_berulang') == 'harian' ? 'selected' : '' }}>Harian</option>
                                <option value="mingguan" {{ old('tipe_berulang') == 'mingguan' ? 'selected' : '' }}>Mingguan</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            {{-- =================== AKHIR BAGIAN BERULANG =================== --}}

            <div class="form-group mb-3">
                <label for="surat_izin" class="form-label fw-bold">Unggah Surat Izin:</label>
                <div class="input-group">
                    {{-- Hidden file input --}}
                    <input class="d-none {{ $errors->has('surat_izin') ? 'is-invalid' : '' }}" 
                        type="file" 
                        name="surat_izin" 
                        id="surat_izin" 
                        accept="application/pdf">

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
                    Surat izin harus berupa file PDF dengan ukuran maksimal 2MB.
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
        // === BAGIAN TOGGLE BERULANG ===
        function checkRecurringToggle() {
            if ($('#toggle-recurring').is(':checked')) {
                $('#recurring-options').show();
            } else {
                $('#recurring-options').hide();
            }
        }

        checkRecurringToggle();

        $('#toggle-recurring').on('change', function() {
            if ($(this).is(':checked')) {
                $('#recurring-options').slideDown();
            } else {
                $('#recurring-options').slideUp();
                $('#berulang_sampai').val('');
                $('#tipe_berulang').val('harian');
            }
        });

        // === BAGIAN FILE UPLOAD (UNGGAH SURAT IZIN) ===
        $('#surat_izin').on('change', function() {
            const fileNameDisplay = $('#surat-izin-display');
            if (this.files.length > 0) {
                fileNameDisplay.text(this.files[0].name);
            } else {
                fileNameDisplay.text("Tidak ada file yang dipilih");
            }
        });

        // === VALIDASI NOMOR TELEPON (CLIENT-SIDE) ===
        // Asumsi: minimal 9 digit, maksimal 15 digit
        const phoneMin = 9;
        const phoneMax = 15;

        function validatePhoneField() {
            const $input = $('#nomor_telepon');
            const $clientError = $('#nomor-telepon-client-error');
            let val = $input.val() || '';

            // Hapus karakter non-digit
            const cleaned = val.replace(/\D/g, '');
            if (val !== cleaned) {
                $input.val(cleaned);
                val = cleaned;
            }

            if (val.length === 0) {
                $clientError.addClass('d-none').text('');
                $input.removeClass('is-invalid');
                return true; // biarkan server cek required
            }

            if (!/^0[0-9]+$/.test(val)) {
                $clientError.removeClass('d-none').text('Nomor telepon harus berupa angka dan dimulai dengan angka 0.');
                $input.addClass('is-invalid');
                return false;
            }

            if (val.length < phoneMin || val.length > phoneMax) {
                $clientError.removeClass('d-none').text(`Panjang nomor harus antara ${phoneMin} sampai ${phoneMax} angka.`);
                $input.addClass('is-invalid');
                return false;
            }

            $clientError.addClass('d-none').text('');
            $input.removeClass('is-invalid');
            return true;
        }

        $('#nomor_telepon').on('input blur', function() {
            validatePhoneField();
        });

        // Prevent form submit if client-side phone validation fails
        $('form').on('submit', function(e) {
            const ok = validatePhoneField();
            if (!ok) {
                e.preventDefault();
                const $firstInvalid = $('#nomor_telepon');
                $('html, body').animate({ scrollTop: $firstInvalid.offset().top - 120 }, 200);
                $firstInvalid.focus();
                return false;
            }
            return true;
        });

        // Toggle datetimepicker when calendar icon clicked
        $('#waktu_mulai_toggle').on('click', function(e) {
            e.preventDefault();
            try {
                $('#waktu_mulai').data('DateTimePicker').show();
            } catch (err) {
                // fallback: focus input
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
@endsection
