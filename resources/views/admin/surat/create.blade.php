@extends('layouts.admin')

@section('styles')
<style>
    /* FIX: Agar preview PDF punya scrollbar dan tidak terpotong */
    #preview-container { 
        background-color: #525659; 
        padding: 30px; 
        height: 800px; /* Tinggi fix biar scrollbar muncul */
        overflow: auto; /* KUNCI: Scrollbar otomatis jika konten lebih besar */
        display: flex; 
        justify-content: center; /* Posisi kertas di tengah */
        align-items: flex-start; 
    }

    /* Kertas A4 */
    #preview-paper { 
        background: white; 
        width: 210mm; 
        min-height: 297mm; 
        /* Hapus padding di sini karena sudah diatur di .surat-wrapper template pdf */
        padding: 0; 
        box-shadow: 0 0 15px rgba(0,0,0,0.5); 
        /* Jangan set font-family disini, biarkan ikut template_pdf */
    }

    .h-78 {
        height: 78%;
        background-color: #525659;
    }

    label.required::after {
        content: " *";
        color: red;
    }

    /* Fix Select2 agar sesuai tema bootstrap (yang tadi) */
    .select2-container { width: 100% !important; display: block; }
    .select2-container .select2-selection--multiple {
        min-height: 38px;
        border: 1px solid #ced4da;
        padding-top: 4px;
        background-color: #fff;
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
</style>
@endsection

@section('content')
<div class="row">
    {{-- FORM INPUT --}}
    <div class="col-lg-5">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-primary">Generator Undangan</h5>
            </div>
            <div class="card-body">
                <form id="suratForm" action="{{ route('admin.surat-undangan.store') }}" method="POST">
                    @csrf
                    
                    {{-- INFO SURAT --}}
                    <h6 class="fw-bold mb-3 text-primary">HEADER SURAT</h6>
                    <div class="mb-3">
                        <label class="form-label required">Nomor Surat</label>
                        <input type="text" name="nomor_surat" class="form-control" placeholder="1518/B/UN3.FTMM/PK.04.00/2025" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Tanggal Surat</label>
                            <input type="date" name="tanggal_surat" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Perihal</label>
                            <input type="text" name="hal_surat" class="form-control" value="Undangan">
                        </div>
                    </div>

                    {{-- TUJUAN (SELECT2 TAGS) --}}
                    {{-- LOGIC TUJUAN: PILIHAN MODE --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">Mode Tujuan Surat</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="mode_tujuan" id="mode_biasa" value="biasa" checked onchange="toggleTujuan()">
                                <label class="form-check-label" for="mode_biasa">Tulis di Surat (Sedikit)</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="mode_tujuan" id="mode_lampiran" value="lampiran" onchange="toggleTujuan()">
                                <label class="form-check-label" for="mode_lampiran">Gunakan Lampiran (Banyak)</label>
                            </div>
                        </div>
                    </div>

                    {{-- INPUT 1: SELECT2 (Mode Biasa) --}}
                    <div class="mb-3" id="container_tujuan_biasa">
                        <label class="form-label required">Tujuan (Yth.)</label>
                        <select name="tujuan_surat[]" id="tujuan_surat" multiple="multiple" style="width: 100%;">
                            {{-- Diisi JS --}}
                        </select>
                    </div>

                    {{-- INPUT 2: TEXTAREA (Mode Lampiran) --}}
                    <div class="mb-3 d-none" id="container_tujuan_lampiran">
                        <label class="form-label required">Isi Lampiran (Copy-Paste dari Excel)</label>
                        <div class="alert alert-info py-2 small">
                            <i class="fas fa-info-circle me-1"></i> Format per baris: <b>Nama Lengkap [pemisah] Jabatan</b>.<br>
                            Contoh: <i>Dr. Budi - Kaprodi SI</i> (Gunakan tanda strip "-" sebagai pemisah).
                        </div>
                        <textarea name="lampiran_content" class="form-control" rows="10" placeholder="Dr. Andi - Kaprodi TSD&#10;Dr. Budi - Kaprodi TRKB&#10;Prof. Siti - Dosen Senior"></textarea>
                    </div>

                    {{-- SCRIPT TOGGLE --}}
                    <script>
                        function toggleTujuan() {
                            let mode = $('input[name="mode_tujuan"]:checked').val();
                            
                            if(mode === 'lampiran') {
                                $('#container_tujuan_biasa').addClass('d-none');
                                $('#container_tujuan_lampiran').removeClass('d-none');
                                // Reset Select2 biar gak konflik validasi
                                $('#tujuan_surat').val(null).trigger('change'); 
                            } else {
                                $('#container_tujuan_biasa').removeClass('d-none');
                                $('#container_tujuan_lampiran').addClass('d-none');
                            }
                            updatePreview();
                        }
                    </script>

                    {{-- DETAIL ACARA --}}
                    <h6 class="fw-bold mt-4 mb-3 text-primary">DETAIL ACARA</h6>
                    
                    {{-- DATE PICKER --}}
                    <div class="mb-3">
                        <label class="form-label required">Tanggal Acara</label>
                        <input type="date" name="tanggal_acara_raw" class="form-control" required>
                        <small class="text-info d-none" id="datePreviewInfo">Akan tertulis: <span></span></small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Waktu (Pukul)</label>
                        <input type="text" name="waktu_acara" class="form-control" placeholder="14.00 - 16.00 WIB" required>
                    </div>

                    {{-- TEMPAT (SELECT2 TAGS) --}}
                    <div class="mb-3">
                        <label class="form-label required">Tempat</label>
                        <select name="tempat_acara" class="form-control select2-tags-single" required>
                            <option value="">-- Pilih atau Ketik --</option>
                            @foreach($tempatPresets as $tempat)
                                <option value="{{ $tempat }}">{{ $tempat }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted" style="font-size: 0.8rem">Klik kotak, lalu <b>ketik di kolom pencarian</b> untuk tambah tempat baru.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Agenda</label>
                        <textarea name="agenda_acara" class="form-control" rows="2" placeholder="Penetapan Yudisium..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Dresscode (opsional)</label>
                        <input type="text" name="dresscode" class="form-control" placeholder="Menggunakan jas rapi">
                    </div>

                    {{-- PENANDATANGAN --}}
                    <h6 class="fw-bold mt-4 mb-3 text-primary">PEJABAT PENANDATANGAN</h6>
                    <div class="mb-3">
                        <label class="form-label">Pilih Pejabat</label>
                        <select name="penandatangan_index" class="form-select select2" onchange="updatePreview()">
                            @foreach($penandatangans as $index => $p)
                                <option value="{{ $index }}">{{ $p['jabatan'] }} - {{ $p['nama'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- FITUR BARU: SWITCH TTD DIGITAL --}}
                    <div class="mb-3">
                        <div class="form-check form-switch p-0">
                            {{-- Class p-0 dan d-flex biar rapi --}}
                            <div class="d-flex align-items-center">
                                <input class="form-check-input ms-0 me-2" type="checkbox" id="use_ttd" name="use_ttd" value="1" style="width: 2.5em; height: 1.5em; cursor: pointer;">
                                <label class="form-check-label fw-bold" for="use_ttd" style="cursor: pointer; margin-top: 2px;">
                                    <span style="margin-left:40px;">Terapkan Tanda Tangan Digital</span>
                                </label>
                            </div>
                            <small class="text-muted d-block mt-1">
                                <i class="fas fa-info-circle me-1"></i>
                                Jika aktif, gambar tanda tangan akan otomatis muncul di dokumen.
                            </small>
                        </div>
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <button type="button" class="btn btn-info text-white" onclick="updatePreview()">
                            <i class="fas fa-sync me-2"></i> Update Preview
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-file-pdf me-2"></i> Download PDF
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- PREVIEW --}}
    <div class="col-lg-7">
        <div class="card shadow-sm h-78">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-eye me-2"></i>Preview Undangan</h5>
                <span class="badge bg-info text-white">A4 Portrait</span>
            </div>
            <div class="card-body p-0">
                <div id="preview-container">
                    <div id="preview-paper">
                        <div class="text-center text-muted py-5 mt-5">
                            <i class="fas fa-file-alt fa-3x mb-3"></i><br>
                            Isi form di samping dan klik "Update Preview"<br>untuk melihat hasil surat.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // --- 1. SIAPKAN DATA ---
        var rawPresets = @json($tujuanPresets);
        console.log("Data Presets:", rawPresets); 

        // --- 2. INISIALISASI SELECT2 ---
        
        // A. SELECT2 TUJUAN (Multiple + Tags + Presets)
        var tujuanOptions = [];
        if(rawPresets && rawPresets.length > 0) {
            tujuanOptions = $.map(rawPresets, function (item) {
                return { id: item, text: item };
            });
        }

        $('#tujuan_surat').select2({
            data: tujuanOptions,
            tags: true, 
            tokenSeparators: [','], 
            placeholder: "Pilih jabatan atau ketik nama custom...",
            allowClear: true,
            width: '100%',
            dropdownParent: $('body'),
            createTag: function (params) {
                var term = $.trim(params.term);
                if (term === '') {
                    return null;
                }
                return {
                    id: term,
                    text: term,
                    newTag: true
                }
            }
        });

        // B. SELECT2 TEMPAT (Single + Tags)
        $('select[name="tempat_acara"]').select2({
            tags: true,
            placeholder: "Pilih ruangan atau ketik lokasi custom...",
            allowClear: true,
            width: '100%',
            minimumResultsForSearch: 0,
            createTag: function (params) {
                var term = $.trim(params.term);
                if (term === '') {
                    return null;
                }
                return {
                    id: term,
                    text: term,
                    newTag: true
                }
            }
        });

        // C. SELECT2 PENANDATANGAN (tanpa tags)
        $('select[name="penandatangan_index"]').select2({ 
            width: '100%',
            minimumResultsForSearch: Infinity // Disable search untuk dropdown biasa
        });
    });

    function updatePreview() {
        var formData = $('#suratForm').serialize();
        $('#preview-paper').html('<div class="text-center py-5 mt-5"><i class="fas fa-circle-notch fa-spin fa-3x text-secondary"></i><br><span class="text-muted mt-2">Menyusun surat...</span></div>');

        $.ajax({
            url: "{{ route('admin.surat-undangan.preview') }}",
            type: "POST",
            data: formData,
            success: function(html) {
                $('#preview-paper').html(html);
            },
            error: function(xhr) {
                if(xhr.status === 422) {
                    alert('Mohon lengkapi data yang bertanda bintang (*)');
                } else {
                    alert('Gagal memuat preview.');
                }
                $('#preview-paper').html('');
            }
        });
    }

    $('input[name="tanggal_acara_raw"]').on('change', function() {
        let val = $(this).val();
        if(val) {
            let date = new Date(val);
            let options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            let formatted = date.toLocaleDateString('id-ID', options);
            $('#datePreviewInfo span').text(formatted);
            $('#datePreviewInfo').removeClass('d-none');
        } else {
            $('#datePreviewInfo').addClass('d-none');
        }
    });
</script>
<script>
    function toggleTujuan() {
        let mode = $('input[name="mode_tujuan"]:checked').val();
        
        if(mode === 'lampiran') {
            $('#container_tujuan_biasa').addClass('d-none');
            $('#container_tujuan_lampiran').removeClass('d-none');
            // Reset Select2 biar gak konflik validasi
            $('#tujuan_surat').val(null).trigger('change'); 
        } else {
            $('#container_tujuan_biasa').removeClass('d-none');
            $('#container_tujuan_lampiran').addClass('d-none');
        }
    }
</script>
@endsection