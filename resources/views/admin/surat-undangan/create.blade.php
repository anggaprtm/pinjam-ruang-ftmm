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
        /* Hanya sebagai container flex, wujud kertas dihandle di template_pdf */
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 30px; 
        width: 100%;
    }
    label.required::after { content: " *"; color: red; }
    .form-section { border-left: 3px solid #0d6efd; padding-left: 12px; margin-bottom: 8px; }
    .sticky-preview {
        position: sticky;
        top: 80px;
    }
    .pegawai-search-wrapper { position:relative; }
    .pegawai-search-wrapper input { padding-left:2.5rem; }
    .pegawai-search-wrapper .search-icon { position:absolute; left:.85rem; top:50%; transform:translateY(-50%); color:#94a3b8; }
    .pegawai-list { max-height:340px; overflow-y:auto; border:1px solid #e2e8f0; border-radius:10px; margin-bottom: 15px; }
    .pegawai-item { display:flex; align-items:flex-start; padding:.85rem 1rem; border-bottom:1px solid #f1f5f9; gap:.75rem; transition:background .15s; }
    .pegawai-item:last-child { border-bottom:none; }
    .pegawai-item:hover { background:#f8fafc; }
    .pegawai-item input[type=checkbox] { width:1.2em; height:1.2em; margin-top: 0.2rem; }
    .pegawai-avatar { width:34px; height:34px; background:#dcfce7; color:#15803d; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:.85rem; flex-shrink:0; }
</style>
@endsection

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0 fw-bold"><i class="fas fa-envelope-open-text me-2 text-primary"></i>Buat Surat Undangan</h4>
        <small class="text-muted">Lengkapi form, cek preview, lalu simpan & download PDF</small>
    </div>
    <a href="{{ route('admin.surat-undangan.index') }}" class="btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left me-1"></i> Kembali ke Arsip
    </a>
</div>

<div class="row g-3">
    {{-- ───── KOLOM KIRI: FORM ───── --}}
    <div class="col-xl-5 col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form id="suratForm" action="{{ route('admin.surat-undangan.store') }}" method="POST">
                    @csrf

                    {{-- SECTION: HEADER SURAT --}}
                    <h6 class="fw-bold mb-3 form-section text-primary">HEADER SURAT</h6>

                    <div class="mb-3">
                        <label class="form-label required small fw-semibold">Nomor Surat</label>
                        <input type="text" name="nomor_surat" class="form-control @error('nomor_surat') is-invalid @enderror"
                               placeholder="Contoh: 1518/B/UN3.FTMM/PK.04.00/2026"
                               value="{{ old('nomor_surat') }}" required>
                        @error('nomor_surat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row g-2">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required small fw-semibold">Tanggal Surat</label>
                            <input type="date" name="tanggal_surat" class="form-control"
                                   value="{{ old('tanggal_surat', date('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-semibold">Perihal</label>
                            <input type="text" name="hal_surat" class="form-control"
                                   value="{{ old('hal_surat', 'Undangan') }}">
                        </div>
                    </div>

                    {{-- SECTION: TUJUAN --}}
                    <h6 class="fw-bold mb-3 mt-4 form-section text-primary">TUJUAN SURAT</h6>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Mode Tujuan</label>
                        <div class="d-flex gap-3 bg-light p-2 rounded border">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="mode_tujuan"
                                       id="mode_biasa" value="biasa" checked onchange="toggleTujuan()">
                                <label class="form-check-label" for="mode_biasa">
                                    <i class="fas fa-list-ul me-1 text-secondary"></i>Tulis di Surat <small class="text-muted">(Sedikit)</small>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="mode_tujuan"
                                       id="mode_lampiran" value="lampiran" onchange="toggleTujuan()">
                                <label class="form-check-label" for="mode_lampiran">
                                    <i class="fas fa-paperclip me-1 text-secondary"></i>Lampiran <small class="text-muted">(Banyak)</small>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div id="container_tujuan_biasa" class="mb-3">
                        <label class="form-label small fw-semibold">Tujuan (Yth.)</label>
                        <select name="tujuan_surat[]" id="tujuan_surat" class="form-select my-select2-tags" multiple="multiple" data-placeholder="Pilih atau ketik nama/jabatan...">
                            @foreach($tujuanPresets as $preset)
                                <option value="{{ $preset }}">{{ $preset }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Ketik nama custom lalu tekan Enter.</small>
                    </div>

                    <div id="container_tujuan_lampiran" class="mb-3 d-none">
                        <label class="form-label small fw-semibold">Pilih Penerima Undangan (Lampiran)</label>
                        
                        {{-- 1. BAGIAN CHECKLIST PEGAWAI --}}
                        <div class="pegawai-search-wrapper mb-2">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" id="pegawaiSearch" class="form-control" placeholder="Cari nama pegawai...">
                        </div>
                        <div class="pegawai-list" id="pegawaiList">
                            @foreach($pegawais as $pegawai)
                                <div class="pegawai-item" data-name="{{ strtolower($pegawai->name) }}">
                                    <input type="checkbox" class="pegawai-check" id="chk_{{ $pegawai->id }}" onchange="togglePegawaiInput(this)">
                                    <div class="pegawai-avatar">{{ strtoupper(substr($pegawai->name, 0, 1)) }}</div>
                                    <div class="flex-grow-1">
                                        <label for="chk_{{ $pegawai->id }}" class="fw-semibold mb-0" style="cursor:pointer;">{{ $pegawai->name }}</label>
                                        <div class="detail-inputs mt-2" style="display:none;">
                                            <div class="p-2 bg-white rounded border">
                                                <input type="hidden" name="lampiran_nama[]" value="{{ $pegawai->name }}" disabled>
                                                @php
                                                    $jabatanOtomatis = '';
                                                    if ($pegawai->tendikDetail) $jabatanOtomatis = $pegawai->tendikDetail->nama_jabatan ?? '';
                                                    elseif ($pegawai->dosenDetail) $jabatanOtomatis = $pegawai->dosenDetail->jabatan_struktural ?? '';
                                                @endphp
                                                <div class="row g-2">
                                                    <div class="col-md-5">
                                                        <label class="small text-muted mb-1">NIP / NIK</label>
                                                        <input type="text" name="lampiran_nip[]" value="{{ $pegawai->nip }}" class="form-control form-control-sm" disabled readonly>
                                                    </div>
                                                    <div class="col-md-7">
                                                        <label class="small text-muted mb-1">Jabatan / Unit</label>
                                                        <input type="text" name="lampiran_jabatan[]" value="{{ $jabatanOtomatis }}" class="form-control form-control-sm" disabled onchange="updatePreview()">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        {{-- 2. BAGIAN TAMBAH MANUAL --}}
                        <div class="d-flex justify-content-between align-items-center mt-3 mb-2">
                            <span class="small fw-bold text-secondary">Data Tambahan (Luar Pegawai)</span>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addManualRow()">
                                <i class="fas fa-plus me-1"></i> Tambah Manual
                            </button>
                        </div>
                        <div id="manual-pegawai-list"></div>
                    </div>

                    {{-- SECTION: DETAIL ACARA --}}
                    <h6 class="fw-bold mb-3 mt-4 form-section text-primary">DETAIL ACARA</h6>

                    <div class="row g-2">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required small fw-semibold">Tanggal Acara</label>
                            <input type="date" name="tanggal_acara_raw" id="tanggal_acara_raw"
                                   class="form-control" value="{{ old('tanggal_acara_raw') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required small fw-semibold">Waktu (Pukul)</label>
                            <input type="text" name="waktu_acara" class="form-control"
                                   placeholder="cth: 09.00 - Selesai WIB" value="{{ old('waktu_acara') }}" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required small fw-semibold">Tempat</label>
                        <select name="tempat_acara" id="tempat_acara" class="form-select my-select2-tags" data-placeholder="-- Pilih atau ketik tempat --">
                            <option value=""></option>
                            @foreach($tempatPresets as $tempat)
                                <option value="{{ $tempat }}" {{ old('tempat_acara') == $tempat ? 'selected' : '' }}>{{ $tempat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required small fw-semibold">Agenda</label>
                        <textarea name="agenda_acara" class="form-control" rows="2"
                                  placeholder="Rapat Koordinasi Akademik..." required>{{ old('agenda_acara') }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Dresscode <small class="text-muted">(opsional)</small></label>
                        <input type="text" name="dresscode" class="form-control"
                               placeholder="Batik / Jas Rapi" value="{{ old('dresscode') }}">
                    </div>

                    {{-- SECTION: PENANDATANGAN --}}
                    <h6 class="fw-bold mb-3 mt-4 form-section text-primary">PENANDATANGAN</h6>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Pejabat Penandatangan</label>
                        <select name="penandatangan_index" id="penandatangan_index" class="form-select my-select2">
                            @foreach($penandatangans as $index => $p)
                                <option value="{{ $index }}" {{ old('penandatangan_index', 0) == $index ? 'selected' : '' }}>
                                    {{ $p['jabatan'] }} — {{ $p['nama'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <div class="form-check form-switch bg-light p-2 rounded border">
                            <input class="form-check-input ms-1 mt-2" type="checkbox" id="use_ttd" name="use_ttd"
                                   value="1" style="width:2.5em; height:1.5em;" {{ old('use_ttd') ? 'checked' : '' }}>
                            <label class="form-check-label ms-3" for="use_ttd">
                                <span class="fw-bold text-dark">Tanda Tangan Digital</span>
                                <small class="text-muted d-block">Gambar TTD akan otomatis dilampirkan.</small>
                            </label>
                        </div>
                    </div>

                    {{-- TOMBOL --}}
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-primary fw-bold" onclick="updatePreview()">
                            <i class="fas fa-sync-alt me-2"></i> Refresh Preview
                        </button>
                        <button type="submit" class="btn btn-success btn-lg fw-bold shadow-sm">
                            <i class="fas fa-save me-2"></i> Simpan & Download PDF
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ───── KOLOM KANAN: PREVIEW ───── --}}
    <div class="col-xl-7 col-lg-6">
        <div class="card border-0 shadow-sm sticky-preview">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-secondary"><i class="fas fa-eye me-2"></i>Live Preview</span>
                <div class="d-flex gap-2 align-items-center">
                    <span class="badge bg-light text-dark border">A4 Portrait</span>
                    <button type="button" class="btn btn-sm btn-primary shadow-sm" onclick="updatePreview()" title="Refresh">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div id="preview-container">
                    <div id="preview-paper">
                        <div class="text-center text-muted py-5" style="padding-top:100px !important;">
                            <i class="fas fa-file-alt fa-3x mb-3 d-block text-light"></i>
                            Memuat preview surat...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- TEMPLATE UNTUK JS MANUAL ROW --}}
<template id="manual-row-template">
    <div class="manual-row border rounded p-2 mb-2 bg-light position-relative">
        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2" onclick="removeManualRow(this)"><i class="fas fa-times"></i></button>
        <div class="row g-2 pe-4">
            <div class="col-md-7">
                <label class="form-label small mb-1 required">Nama Lengkap & Gelar</label>
                <input type="text" name="lampiran_nama[]" class="form-control form-control-sm" required onchange="updatePreview()">
            </div>
            <div class="col-md-5">
                <label class="form-label small mb-1">NIP (Opsional)</label>
                <input type="text" name="lampiran_nip[]" class="form-control form-control-sm" placeholder="Kosongkan jika tdk ada" onchange="updatePreview()">
            </div>
            <div class="col-md-12">
                <label class="form-label small mb-1">Jabatan / Instansi</label>
                <input type="text" name="lampiran_jabatan[]" class="form-control form-control-sm" placeholder="Misal: Dosen Universitas Brawijaya" onchange="updatePreview()">
            </div>
        </div>
    </div>
</template>
@endsection

@section('scripts')
@parent
<script>
$(document).ready(function () {

    // ── Inisialisasi Select2 Tema BS5 ──
    $('.my-select2').select2({
        theme: 'bootstrap-5',
        width: '100%',
        minimumResultsForSearch: Infinity
    });

    $('.my-select2-tags').select2({
        theme: 'bootstrap-5',
        width: '100%',
        tags: true,
        tokenSeparators: [','],
        allowClear: true,
        createTag: function (params) {
            var term = $.trim(params.term);
            return term === '' ? null : { id: term, text: term, newTag: true };
        }
    });

    // ── Trigger preview saat ada perubahan ──
    $('input[name="tanggal_surat"], input[name="tanggal_acara_raw"], input[name="waktu_acara"], textarea[name="agenda_acara"]')
        .on('change blur', function() { updatePreview(); });

    $('select[name="penandatangan_index"], input[name="use_ttd"], input[name="mode_tujuan"]')
        .on('change', function () { updatePreview(); });

    // ── Teks Tanggal Otomatis ──
    function formatTanggalIndo(dateString) {
        if (!dateString) return '';
        var parts = dateString.split('-');
        var date = new Date(parts[0], parts[1] - 1, parts[2]); 
        return new Intl.DateTimeFormat('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }).format(date);
    }

    var dateInputs = ['tanggal_surat', 'tanggal_acara_raw'];
    dateInputs.forEach(function(name) {
        var input = document.querySelector('input[name="' + name + '"]');
        if (!input) return;

        var helperText = document.createElement('small');
        helperText.className = 'text-primary d-block mt-1';
        helperText.style.fontWeight = '500';
        input.parentNode.insertBefore(helperText, input.nextSibling);

        function updateHelper() {
            if (input.value) {
                helperText.innerHTML = '<i class="fas fa-calendar-check me-1"></i> Tertulis: ' + formatTanggalIndo(input.value);
            } else {
                helperText.innerHTML = '';
            }
        }
        input.addEventListener('change', updateHelper);
        input.addEventListener('input', updateHelper);
        updateHelper(); 
    });

    // Load awal preview
    updatePreview();
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
document.getElementById('pegawaiSearch').addEventListener('input', function () {
        var q = this.value.toLowerCase();
        document.querySelectorAll('#pegawaiList .pegawai-item').forEach(function (el) {
            el.style.display = el.dataset.name.includes(q) ? '' : 'none';
        });
    });

    function togglePegawaiInput(checkbox) {
        var container = checkbox.closest('.pegawai-item').querySelector('.detail-inputs');
        var inputs = container.querySelectorAll('input');
        if (checkbox.checked) {
            container.style.display = 'block';
            inputs.forEach(inp => inp.removeAttribute('disabled'));
        } else {
            container.style.display = 'none';
            inputs.forEach(inp => inp.setAttribute('disabled', 'disabled'));
        }
        updatePreview();
    }

    function addManualRow() {
        var clone = document.getElementById('manual-row-template').content.cloneNode(true);
        document.getElementById('manual-pegawai-list').appendChild(clone);
        updatePreview();
    }
    function removeManualRow(btn) {
        btn.closest('.manual-row').remove();
        updatePreview();
    }
</script>
@endsection