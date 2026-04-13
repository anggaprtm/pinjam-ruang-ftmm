@extends('layouts.admin')

@section('styles')
<style>
    #preview-container {
        background: #525659;
        padding: 20px;
        height: calc(100vh - 180px);
        min-height: 700px;
        overflow: auto;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        border-radius: 0 0 .375rem .375rem;
    }
    #preview-paper {
        background: white;
        width: 210mm;
        min-height: 297mm;
        box-shadow: 0 0 20px rgba(0,0,0,.6);
        flex-shrink: 0;
    }
    label.required::after { content: " *"; color: red; }
    .form-section { border-left: 3px solid #198754; padding-left: 12px; margin-bottom: 8px; }
    .sticky-preview { position: sticky; top: 76px; }
    .select2-container { width: 100% !important; display: block; }
    .select2-container .select2-selection--single {
        height: 38px !important; border: 1px solid #ced4da; display: flex; align-items: center;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow { top: 6px; }
    .pegawai-search-wrapper { position: relative; }
    .pegawai-search-wrapper input { padding-left: 2.5rem; }
    .pegawai-search-wrapper .search-icon { position: absolute; left: .85rem; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none; }
    .pegawai-checklist { max-height: 320px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: .5rem; }
    .pegawai-item { display: flex; align-items: flex-start; padding: .75rem 1rem; border-bottom: 1px solid #f1f5f9; gap: .75rem; transition: background .12s; }
    .pegawai-item:last-child { border-bottom: none; }
    .pegawai-item:hover { background: #f8fafc; }
    .pegawai-item input[type=checkbox] { width: 1.15em; height: 1.15em; margin-top: .2rem; flex-shrink: 0; cursor: pointer; }
    .pegawai-avatar { width: 32px; height: 32px; background: #dcfce7; color: #15803d; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: .8rem; flex-shrink: 0; }
    .detail-inputs { margin-top: .5rem; }
    .manual-row { background: #f8f9fa; border-radius: .375rem; padding: 10px 12px; margin-bottom: 8px; position: relative; border: 1px solid #dee2e6; }
    .manual-row .btn-remove { position: absolute; top: 8px; right: 8px; }
</style>
@endsection

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0 fw-bold"><i class="fas fa-edit me-2 text-warning"></i>Edit Surat Tugas</h4>
        <small class="text-muted">Nomor: <strong>{{ $surat->nomor_surat ?: '(Belum Bernomor)' }}</strong></small>
    </div>
    <a href="{{ route('admin.surat-tugas.index') }}" class="btn btn-sm btn-secondary">
        <i class="fas fa-arrow-left me-1"></i> Kembali
    </a>
</div>

{{-- NIP pegawai yang sudah ditugaskan — untuk pre-check --}}
@php
    $existingNips     = collect($surat->pegawai_array)->pluck('nip')->filter()->values()->toArray();
    $existingPegawais = collect($surat->pegawai_array)->keyBy('nip')->toArray();
    // Pegawai yang tersimpan tapi tidak ada di daftar sistem (manual/tamu)
    $systemNips = $pegawais->pluck('nip')->toArray();
    $manualRows = collect($surat->pegawai_array)->filter(fn($p) => !in_array($p['nip'], $systemNips))->values();
@endphp

<div class="row g-3">

    {{-- ══════ FORM ══════ --}}
    <div class="col-xl-5 col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form id="suratForm"
                      action="{{ route('admin.surat-tugas.update', $surat->id) }}"
                      method="POST">
                    @csrf @method('PUT')

                    {{-- HEADER --}}
                    <h6 class="fw-bold mb-3 form-section text-success">HEADER SURAT</h6>

                    <div class="mb-3">
                        <label class="form-label">
                            Nomor Surat
                            @if(empty($surat->nomor_surat))
                                <span class="badge bg-danger ms-1" style="font-size:.7rem;">Belum Bernomor</span>
                            @endif
                        </label>
                        <input type="text" name="nomor_surat" class="form-control"
                               placeholder="Isi setelah mendapat dari e-office UNAIR"
                               value="{{ old('nomor_surat', $surat->nomor_surat) }}">
                        <small class="text-muted">Kosongkan jika belum mendapat nomor dari e-office.</small>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Tanggal Surat</label>
                            <input type="date" name="tanggal_surat" class="form-control"
                                   value="{{ old('tanggal_surat', $surat->tanggal_surat->format('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Perihal</label>
                            <input type="text" name="hal_surat" class="form-control"
                                   value="{{ old('hal_surat', $surat->hal_surat) }}">
                        </div>
                    </div>

                    {{-- DETAIL ACARA --}}
                    <h6 class="fw-bold mb-3 mt-1 form-section text-success">DETAIL ACARA / TUGAS</h6>

                    <div class="mb-3">
                        <label class="form-label required">Nama Acara / Tugas</label>
                        <textarea name="isi_tugas" class="form-control" rows="2" required>{{ old('isi_tugas', $surat->isi_tugas) }}</textarea>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Tanggal Mulai</label>
                            <input type="date" name="tanggal_tugas_raw" class="form-control"
                                   value="{{ old('tanggal_tugas_raw', $surat->tanggal_tugas_raw) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Selesai</label>
                            <input type="date" name="tanggal_tugas_akhir_raw" class="form-control"
                                   value="{{ old('tanggal_tugas_akhir_raw', $surat->tanggal_tugas_akhir_raw) }}">
                        </div>
                    </div>
                    <small class="text-muted d-block mb-3">
                        Tertulis di surat: <strong>{{ $surat->hari_tanggal_tugas }}</strong>
                    </small>

                    <div class="mb-3">
                        <label class="form-label required">Waktu Pelaksanaan</label>
                        <input type="text" name="waktu_tugas" class="form-control"
                               value="{{ old('waktu_tugas', $surat->waktu_tugas) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Tempat / Tujuan</label>
                        <select name="tempat_tugas" id="tempat_tugas" class="form-control" required>
                            <option value="">-- Pilih atau ketik --</option>
                            @foreach($tempatPresets as $t)
                                <option value="{{ $t }}" {{ old('tempat_tugas', $surat->tempat_tugas) == $t ? 'selected' : '' }}>{{ $t }}</option>
                            @endforeach
                            @if(!in_array($surat->tempat_tugas, $tempatPresets))
                                <option value="{{ $surat->tempat_tugas }}" selected>{{ $surat->tempat_tugas }}</option>
                            @endif
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Dasar Penugasan <small class="text-muted">(opsional)</small></label>
                        <select name="dasar_surat" id="dasar_surat" class="form-control">
                            <option value="">-- Kosongkan jika tidak ada --</option>
                            @foreach($dasarPresets as $d)
                                <option value="{{ $d }}" {{ old('dasar_surat', $surat->dasar_surat) == $d ? 'selected' : '' }}>{{ $d }}</option>
                            @endforeach
                            @if($surat->dasar_surat && !in_array($surat->dasar_surat, $dasarPresets))
                                <option value="{{ $surat->dasar_surat }}" selected>{{ $surat->dasar_surat }}</option>
                            @endif
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Pakaian <small class="text-muted">(opsional)</small></label>
                        <input type="text" name="pakaian" class="form-control"
                               value="{{ old('pakaian', $surat->pakaian) }}"
                               placeholder="Jas Hitam FTMM / Pakaian Dinas Harian">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Keterangan <small class="text-muted">(opsional)</small></label>
                        <textarea name="keterangan" class="form-control" rows="2">{{ old('keterangan', $surat->keterangan) }}</textarea>
                    </div>

                    {{-- PEGAWAI --}}
                    <h6 class="fw-bold mb-3 mt-2 form-section text-success">PEGAWAI YANG DITUGASKAN</h6>

                    <div class="pegawai-search-wrapper mb-2">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" id="pegawaiSearch" class="form-control form-control-sm"
                               placeholder="Cari nama pegawai...">
                    </div>

                    <div class="pegawai-checklist mb-3" id="pegawaiList">
                        @forelse($pegawais as $pegawai)
                            @php
                                $isChecked = in_array($pegawai->nip, $existingNips);
                                
                                $jabatanOtomatis = '';
                                if ($pegawai->tendikDetail) {
                                    $jabatanOtomatis = $pegawai->tendikDetail->nama_jabatan ?? '';
                                } elseif ($pegawai->dosenDetail) {
                                    $jabatanOtomatis = $pegawai->dosenDetail->jabatan_struktural ?? '';
                                }
                                
                                $savedJabatan = $isChecked ? ($existingPegawais[$pegawai->nip]['jabatan'] ?? '') : $jabatanOtomatis;
                            @endphp
                            <div class="pegawai-item" data-name="{{ strtolower($pegawai->name) }}">
                                <input type="checkbox" class="pegawai-check" id="chk_{{ $pegawai->id }}"
                                       onchange="togglePegawaiInput(this)"
                                       {{ $isChecked ? 'checked' : '' }}>
                                <div class="pegawai-avatar">{{ strtoupper(substr($pegawai->name, 0, 1)) }}</div>
                                <div class="flex-grow-1">
                                    <label for="chk_{{ $pegawai->id }}" class="fw-semibold mb-0 d-block" style="cursor:pointer; line-height:1.3;">
                                        {{ $pegawai->name }}
                                    </label>
                                    <div class="text-muted" style="font-size:.78rem;">{{ $pegawai->nip }}</div>
                                    <div class="detail-inputs" style="{{ $isChecked ? '' : 'display:none;' }}">
                                        <div class="p-2 bg-light rounded border">
                                            <input type="hidden" name="pegawai_nama[]" value="{{ $pegawai->name }}" {{ $isChecked ? '' : 'disabled' }}>
                                            <input type="hidden" name="pegawai_nip[]"  value="{{ $pegawai->nip }}"  {{ $isChecked ? '' : 'disabled' }}>
                                            <label class="small text-muted mb-1 d-block">Jabatan / Unit</label>
                                            <input type="text" name="pegawai_jabatan[]"
                                                   value="{{ $savedJabatan }}"
                                                   class="form-control form-control-sm"
                                                   {{ $isChecked ? '' : 'disabled' }}
                                                   placeholder="Jabatan di FTMM">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted small py-3">Tidak ada data pegawai.</div>
                        @endforelse
                    </div>

                    {{-- Manual row dari data lama --}}
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-semibold text-secondary">
                            <i class="fas fa-user-plus me-1"></i>Tambah dari luar (tamu/magang/panitia luar)
                        </span>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addManualRow()">
                            <i class="fas fa-plus me-1"></i> Tambah
                        </button>
                    </div>
                    <div id="manual-pegawai-list">
                        @foreach($manualRows as $m)
                        <div class="manual-row">
                            <button type="button" class="btn btn-xs btn-outline-danger btn-remove" onclick="removeManualRow(this)">
                                <i class="fas fa-times"></i>
                            </button>
                            <div class="row g-2">
                                <div class="col-12">
                                    <label class="form-label small fw-semibold mb-1 required">Nama Lengkap</label>
                                    <input type="text" name="pegawai_nama[]" class="form-control form-control-sm"
                                           value="{{ $m['nama'] }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small mb-1">NIP / NIK / NIM</label>
                                    <input type="text" name="pegawai_nip[]" class="form-control form-control-sm"
                                           value="{{ $m['nip'] ?? '' }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small mb-1">Jabatan / Instansi</label>
                                    <input type="text" name="pegawai_jabatan[]" class="form-control form-control-sm"
                                           value="{{ $m['jabatan'] ?? '' }}">
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- PENANDATANGAN --}}
                    <h6 class="fw-bold mb-3 mt-3 form-section text-success">PENANDATANGAN</h6>

                    <div class="mb-3">
                        <label class="form-label">Pejabat Penandatangan</label>
                        <select name="penandatangan_index" id="penandatangan_index" class="form-select">
                            @foreach($penandatangans as $index => $p)
                                <option value="{{ $index }}" {{ old('penandatangan_index', 0) == $index ? 'selected' : '' }}>
                                    {{ $p['jabatan'] }} — {{ $p['nama'] }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Sebelumnya: <strong>{{ $surat->jabatan_penandatangan }} — {{ $surat->nama_penandatangan }}</strong></small>
                    </div>

                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="use_ttd" name="use_ttd"
                                   value="1" style="width:2.5em;height:1.5em;">
                            <label class="form-check-label ms-2 fw-semibold" for="use_ttd">Tanda Tangan Digital</label>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-success" onclick="updatePreview()">
                            <i class="fas fa-sync me-2"></i>Refresh Preview
                        </button>
                        <button type="submit" class="btn btn-warning btn-lg text-white">
                            <i class="fas fa-save me-2"></i>Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ══════ PREVIEW ══════ --}}
    <div class="col-xl-7 col-lg-6">
        <div class="card border-0 shadow-sm sticky-preview">
            <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="fas fa-eye me-2 text-secondary"></i>Preview Surat Tugas</span>
                <div class="d-flex gap-2 align-items-center">
                    <span class="badge bg-light text-dark border" style="font-size:.7rem;">A4 • 2 halaman</span>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="updatePreview()">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div id="preview-container">
                    <div id="preview-paper">
                        <div class="text-center text-muted py-5" style="padding-top:80px!important;font-family:sans-serif;">
                            <i class="fas fa-file-signature fa-3x mb-3 d-block text-secondary"></i>
                            Klik <strong>"Refresh Preview"</strong> untuk melihat surat.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<template id="manual-row-template">
    <div class="manual-row">
        <button type="button" class="btn btn-xs btn-outline-danger btn-remove" onclick="removeManualRow(this)">
            <i class="fas fa-times"></i>
        </button>
        <div class="row g-2">
            <div class="col-12">
                <label class="form-label small fw-semibold mb-1 required">Nama Lengkap</label>
                <input type="text" name="pegawai_nama[]" class="form-control form-control-sm" required>
            </div>
            <div class="col-md-6">
                <label class="form-label small mb-1">NIP / NIK / NIM</label>
                <input type="text" name="pegawai_nip[]" class="form-control form-control-sm">
            </div>
            <div class="col-md-6">
                <label class="form-label small mb-1">Jabatan / Instansi</label>
                <input type="text" name="pegawai_jabatan[]" class="form-control form-control-sm">
            </div>
        </div>
    </div>
</template>

@endsection

@section('scripts')
@parent
<script>
$(document).ready(function () {
    $('#tempat_tugas').select2({
        tags: true, placeholder: 'Pilih atau ketik...', allowClear: true, width: '100%',
        createTag: function (p) { var t = $.trim(p.term); return t==='' ? null : {id:t,text:t,newTag:true}; }
    });
    $('#dasar_surat').select2({
        tags: true, placeholder: 'Pilih atau ketik...', allowClear: true, width: '100%',
        createTag: function (p) { var t = $.trim(p.term); return t==='' ? null : {id:t,text:t,newTag:true}; }
    });
    $('#penandatangan_index').select2({ width: '100%', minimumResultsForSearch: Infinity });

    // Auto preview on load
    updatePreview();
});

function togglePegawaiInput(checkbox) {
    var item   = checkbox.closest('.pegawai-item');
    var detail = item.querySelector('.detail-inputs');
    var inputs = detail.querySelectorAll('input');
    if (checkbox.checked) {
        detail.style.display = 'block';
        inputs.forEach(inp => inp.removeAttribute('disabled'));
    } else {
        detail.style.display = 'none';
        inputs.forEach(inp => inp.setAttribute('disabled', 'disabled'));
    }
}

document.getElementById('pegawaiSearch').addEventListener('input', function () {
    var q = this.value.toLowerCase();
    document.querySelectorAll('#pegawaiList .pegawai-item').forEach(function (el) {
        el.style.display = el.dataset.name.includes(q) ? '' : 'none';
    });
});

function addManualRow() {
    var clone = document.getElementById('manual-row-template').content.cloneNode(true);
    document.getElementById('manual-pegawai-list').appendChild(clone);
}
function removeManualRow(btn) { btn.closest('.manual-row').remove(); }

function updatePreview() {
    $('#preview-paper').html(
        '<div class="text-center py-5" style="padding-top:100px!important;font-family:sans-serif">' +
        '<i class="fas fa-circle-notch fa-spin fa-2x text-secondary"></i>' +
        '<div class="text-muted mt-2">Menyusun surat...</div></div>'
    );

    // Filter data form agar '_method=PUT' TIDAK ikut terkirim ke route POST preview
    var formData = $('#suratForm').serializeArray();
    formData = formData.filter(function(item) {
        return item.name !== '_method';
    });

    $.ajax({
        url:  "{{ route('admin.surat-tugas.preview') }}",
        type: 'POST',
        data: $.param(formData), // Gunakan formData yang sudah difilter
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function (html) { $('#preview-paper').html(html); },
        error: function (xhr) {
            var msg = xhr.status === 422 ? 'Lengkapi field wajib (*).' : 'Gagal memuat preview.';
            $('#preview-paper').html('<div class="alert alert-warning m-3">' + msg + '</div>');
        }
    });
}
// ── Script Preview Tanggal Otomatis ──
document.addEventListener('DOMContentLoaded', function() {
    function formatTanggalIndo(dateString) {
        if (!dateString) return '';
        // Hindari bug timezone (seperti -1 hari) dengan parsing manual
        var parts = dateString.split('-');
        var date = new Date(parts[0], parts[1] - 1, parts[2]); 
        
        return new Intl.DateTimeFormat('id-ID', { 
            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' 
        }).format(date);
    }

    // Targetkan 3 input tanggal
    var dateInputs = ['tanggal_surat', 'tanggal_tugas_raw', 'tanggal_tugas_akhir_raw'];
    
    dateInputs.forEach(function(name) {
        var input = document.querySelector('input[name="' + name + '"]');
        if (!input) return;

        // Buat elemen text di bawah input
        var helperText = document.createElement('small');
        helperText.className = 'text-primary d-block mt-1';
        helperText.style.fontWeight = '500';
        // Sisipkan setelah input (atau setelah invalid-feedback jika ada)
        input.parentNode.insertBefore(helperText, input.nextSibling);

        function updateHelper() {
            if (input.value) {
                helperText.innerHTML = '<i class="fas fa-calendar-check me-1"></i> Tertulis: ' + formatTanggalIndo(input.value);
            } else {
                helperText.innerHTML = '';
            }
        }

        // Jalankan saat diubah dan saat halaman pertama dimuat
        input.addEventListener('change', updateHelper);
        input.addEventListener('input', updateHelper);
        updateHelper(); 
    });
});
</script>
@endsection