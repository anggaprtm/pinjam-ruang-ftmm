@extends('layouts.admin')

@section('styles')
<style>
    /* ── Preview container ── */
    #preview-container {
        background: #525659;
        padding: 20px;
        height: calc(100vh - 180px);
        min-height: 700px;
        overflow: auto;
        border-radius: 0 0 .375rem .375rem;
    }
    #preview-paper {
        /* Sekarang preview paper hanya jadi container flex, bukan wujud kertas lagi */
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 30px; /* Ini jarak antar lembar kertasnya nanti */
        width: 100%;
    }
    label.required::after { content: " *"; color: red; }
    .select2-container { width: 100% !important; display: block; }
    .select2-container .select2-selection--multiple {
        min-height: 38px;
        border: 1px solid #ced4da;
        padding-top: 3px;
    }
    .select2-container .select2-selection--single {
        height: 38px !important;
        border: 1px solid #ced4da;
        display: flex;
        align-items: center;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        top: 6px;
    }
    .form-section { border-left: 3px solid #0d6efd; padding-left: 12px; margin-bottom: 8px; }
    .sticky-preview {
        position: sticky;
        top: 80px;
    }
</style>
@endsection

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0 fw-bold"><i class="fas fa-envelope me-2 text-primary"></i>Buat Surat Undangan</h4>
        <small class="text-muted">Isi form, preview, lalu download PDF</small>
    </div>
    <a href="{{ route('admin.surat-undangan.index') }}" class="btn btn-sm btn-secondary">
        <i class="fas fa-arrow-left me-1"></i> Kembali ke Arsip
    </a>
</div>

<div class="row g-3">

    {{-- ───── KOLOM KIRI: FORM ───── --}}
    <div class="col-xl-5 col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form id="suratForm" action="{{ route('admin.surat-undangan.store') }}" method="POST">
                    @csrf

                    {{-- SECTION: HEADER SURAT --}}
                    <h6 class="fw-bold mb-3 form-section text-primary">HEADER SURAT</h6>

                    <div class="mb-3">
                        <label class="form-label required">Nomor Surat</label>
                        <input type="text" name="nomor_surat" class="form-control @error('nomor_surat') is-invalid @enderror"
                               placeholder="1518/B/UN3.FTMM/PK.04.00/2025"
                               value="{{ old('nomor_surat') }}" required>
                        @error('nomor_surat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row g-2">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Tanggal Surat</label>
                            <input type="date" name="tanggal_surat" class="form-control"
                                   value="{{ old('tanggal_surat', date('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Perihal</label>
                            <input type="text" name="hal_surat" class="form-control"
                                   value="{{ old('hal_surat', 'Undangan') }}">
                        </div>
                    </div>

                    {{-- SECTION: TUJUAN --}}
                    <h6 class="fw-bold mb-3 mt-2 form-section text-primary">TUJUAN SURAT</h6>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mode Tujuan</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="mode_tujuan"
                                       id="mode_biasa" value="biasa" checked onchange="toggleTujuan()">
                                <label class="form-check-label" for="mode_biasa">
                                    <i class="fas fa-list-ul me-1 text-muted"></i>Tulis di Surat <small class="text-muted">(≤5 orang)</small>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="mode_tujuan"
                                       id="mode_lampiran" value="lampiran" onchange="toggleTujuan()">
                                <label class="form-check-label" for="mode_lampiran">
                                    <i class="fas fa-paperclip me-1 text-muted"></i>Lampiran <small class="text-muted">(banyak orang)</small>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div id="container_tujuan_biasa" class="mb-3">
                        <label class="form-label">Tujuan (Yth.)</label>
                        <select name="tujuan_surat[]" id="tujuan_surat" multiple="multiple">
                            @foreach($tujuanPresets as $preset)
                                <option value="{{ $preset }}">{{ $preset }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Pilih dari daftar atau ketik nama custom lalu tekan Enter.</small>
                    </div>

                    <div id="container_tujuan_lampiran" class="mb-3 d-none">
                        <label class="form-label">Daftar Penerima Undangan <small class="text-muted">(format: Nama - Jabatan)</small></label>
                        <div class="alert alert-info py-2 mb-2 small">
                            <i class="fas fa-info-circle me-1"></i>
                            Satu baris = satu orang. Gunakan tanda <strong>-</strong> sebagai pemisah nama dan jabatan.<br>
                            Contoh: <code>Dr. Andi Santoso - Koordinator Prodi TSD</code>
                        </div>
                        <textarea name="lampiran_content" class="form-control font-monospace" rows="8"
                                  placeholder="Dr. Andi Santoso - Koordinator Prodi TSD&#10;Prof. Budi Utomo - Dosen Senior&#10;Siti Aminah, M.Kom - Staf Akademik">{{ old('lampiran_content') }}</textarea>
                    </div>

                    {{-- SECTION: DETAIL ACARA --}}
                    <h6 class="fw-bold mb-3 mt-2 form-section text-primary">DETAIL ACARA</h6>

                    <div class="mb-3">
                        <label class="form-label required">Tanggal Acara</label>
                        <input type="date" name="tanggal_acara_raw" id="tanggal_acara_raw"
                               class="form-control" value="{{ old('tanggal_acara_raw') }}" required>
                        <small class="text-info d-none" id="datePreviewInfo">
                            <i class="fas fa-calendar-check me-1"></i>Akan tertulis: <strong id="datePreviewText"></strong>
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Waktu (Pukul)</label>
                        <input type="text" name="waktu_acara" class="form-control"
                               placeholder="14.00 - 16.00 WIB" value="{{ old('waktu_acara') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Tempat</label>
                        <select name="tempat_acara" id="tempat_acara" class="form-control">
                            <option value="">-- Pilih atau ketik tempat --</option>
                            @foreach($tempatPresets as $tempat)
                                <option value="{{ $tempat }}" {{ old('tempat_acara') == $tempat ? 'selected' : '' }}>{{ $tempat }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Klik lalu ketik untuk menambah lokasi baru.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Agenda</label>
                        <textarea name="agenda_acara" class="form-control" rows="2"
                                  placeholder="Rapat Koordinasi Akademik Semester Ganjil 2025/2026" required>{{ old('agenda_acara') }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Dresscode <small class="text-muted">(opsional)</small></label>
                        <input type="text" name="dresscode" class="form-control"
                               placeholder="Batik / Jas Rapi" value="{{ old('dresscode') }}">
                    </div>

                    {{-- SECTION: PENANDATANGAN --}}
                    <h6 class="fw-bold mb-3 mt-2 form-section text-primary">PENANDATANGAN</h6>

                    <div class="mb-3">
                        <label class="form-label">Pejabat Penandatangan</label>
                        <select name="penandatangan_index" id="penandatangan_index" class="form-select">
                            @foreach($penandatangans as $index => $p)
                                <option value="{{ $index }}" {{ old('penandatangan_index', 0) == $index ? 'selected' : '' }}>
                                    {{ $p['jabatan'] }} — {{ $p['nama'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="use_ttd" name="use_ttd"
                                   value="1" style="width:2.5em; height:1.5em;" {{ old('use_ttd') ? 'checked' : '' }}>
                            <label class="form-check-label ms-2" for="use_ttd">
                                <span class="fw-semibold">Tanda Tangan Digital</span>
                                <small class="text-muted d-block">Gambar tanda tangan otomatis ditambahkan di dokumen.</small>
                            </label>
                        </div>
                    </div>

                    {{-- TOMBOL --}}
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-primary" onclick="updatePreview()">
                            <i class="fas fa-sync me-2"></i> Refresh Preview
                        </button>
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-file-pdf me-2"></i> Simpan & Download PDF
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ───── KOLOM KANAN: PREVIEW ───── --}}
    <div class="col-xl-7 col-lg-6">
        <div class="card border-0 shadow-sm sticky-preview">
            <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="fas fa-eye me-2 text-secondary"></i>Preview Surat</span>
                <div class="d-flex gap-2 align-items-center">
                    <span class="badge bg-light text-dark border">A4 Portrait</span>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="updatePreview()">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div id="preview-container">
                    <div id="preview-paper">
                        <div class="text-center text-muted py-5" style="padding-top:80px !important;">
                            <i class="fas fa-file-alt fa-3x mb-3 d-block"></i>
                            Isi form di samping lalu klik<br><strong>"Refresh Preview"</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@parent
<script>
$(document).ready(function () {

    // ── SELECT2: TUJUAN (Multiple + Tags) ──
    $('#tujuan_surat').select2({
        tags: true,
        tokenSeparators: [','],
        placeholder: 'Pilih jabatan atau ketik custom...',
        allowClear: true,
        width: '100%',
        dropdownParent: $('body'),
        createTag: function (params) {
            var term = $.trim(params.term);
            return term === '' ? null : { id: term, text: term, newTag: true };
        }
    });

    // ── SELECT2: TEMPAT (Single + Tags) ──
    $('#tempat_acara').select2({
        tags: true,
        placeholder: 'Pilih ruangan atau ketik lokasi...',
        allowClear: true,
        width: '100%',
        createTag: function (params) {
            var term = $.trim(params.term);
            return term === '' ? null : { id: term, text: term, newTag: true };
        }
    });

    // ── SELECT2: PENANDATANGAN ──
    $('#penandatangan_index').select2({
        width: '100%',
        minimumResultsForSearch: Infinity
    });

    // ── Trigger preview on important changes ──
    $('input[name="tanggal_surat"], input[name="tanggal_acara_raw"], input[name="waktu_acara"], textarea[name="agenda_acara"]')
        .on('change blur', function() { updatePreview(); });

    $('select[name="penandatangan_index"], input[name="use_ttd"]')
        .on('change', function () { updatePreview(); });

    // ── Date preview helper ──
    $('input[name="tanggal_acara_raw"]').on('change', function () {
        var val = $(this).val();
        if (val) {
            var days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
            var months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
            var d = new Date(val);
            var formatted = days[d.getDay()] + ', ' + d.getDate() + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();
            $('#datePreviewText').text(formatted);
            $('#datePreviewInfo').removeClass('d-none');
        } else {
            $('#datePreviewInfo').addClass('d-none');
        }
    });
});

function toggleTujuan() {
    var mode = $('input[name="mode_tujuan"]:checked').val();
    if (mode === 'lampiran') {
        $('#container_tujuan_biasa').addClass('d-none');
        $('#container_tujuan_lampiran').removeClass('d-none');
        $('#tujuan_surat').val(null).trigger('change');
    } else {
        $('#container_tujuan_biasa').removeClass('d-none');
        $('#container_tujuan_lampiran').addClass('d-none');
    }
    updatePreview();
}

function updatePreview() {
    $('#preview-paper').html(
        '<div class="text-center py-5" style="padding-top:100px!important">' +
        '<i class="fas fa-circle-notch fa-spin fa-2x text-secondary"></i>' +
        '<div class="text-muted mt-2">Menyusun surat...</div></div>'
    );

    $.ajax({
        url: "{{ route('admin.surat-undangan.preview') }}",
        type: "POST",
        data: $('#suratForm').serialize(),
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function (html) {
            $('#preview-paper').html(html);
        },
        error: function (xhr) {
            var msg = xhr.status === 422
                ? 'Mohon lengkapi field yang wajib diisi (*).'
                : 'Gagal memuat preview. Silakan coba lagi.';
            $('#preview-paper').html('<div class="alert alert-warning m-3">' + msg + '</div>');
        }
    });
}
</script>
@endsection