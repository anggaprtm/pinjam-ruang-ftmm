@extends('layouts.admin')
@section('content')

<div class="card form-card">
    <div class="card-header">
        <h4 class="mb-0"><i class="fas fa-paper-plane me-2"></i> Buat Permintaan Kegiatan & Konsumsi</h4>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.permintaan-kegiatan.store") }}" enctype="multipart/form-data">
            @csrf
            
            {{-- SECTION 1: JENIS PERMINTAAN --}}
            <div class="alert alert-info bg-light-info border-0 mb-4">
                <div class="fw-bold mb-2">Jenis Layanan yang Dibutuhkan:</div>

                <div class="d-flex flex-column flex-md-row gap-2 gap-md-4">
                    {{-- Peminjaman Ruang --}}
                    <div class="form-check form-switch m-0">
                        <input class="form-check-input" type="checkbox" name="request_ruang" id="req_ruang" checked>
                        <label class="form-check-label fw-bold " for="req_ruang">
                            <i class="fas me-2"></i>
                            Peminjaman Ruang
                        </label>
                    </div>

                    {{-- Permintaan Konsumsi --}}
                    <div class="form-check form-switch m-0">
                        <input class="form-check-input" type="checkbox" name="request_konsumsi" id="req_konsumsi" checked>
                        <label class="form-check-label fw-bold " for="req_konsumsi">
                            <i class="fas me-2"></i>
                            Permintaan Konsumsi
                        </label>
                    </div>
                </div>
            </div>



            <div class="row">
                {{-- KOLOM KIRI: DATA UTAMA --}}
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="required" for="nama_kegiatan">Nama Kegiatan</label>
                        <input class="form-control" type="text" name="nama_kegiatan" required placeholder="Contoh: Rapat Koordinasi Semester Genap">
                    </div>

                    <div class="form-group mb-3">
                        <label class="required" for="jenis_kegiatan">Jenis Kegiatan</label>
                        <select class="form-control select2" name="jenis_kegiatan" required>
                            @foreach(['Rapat', 'Seminar Proposal', 'Sidang Skripsi', 'Kegiatan Ormawa', 'Lainnya'] as $jenis)
                                <option value="{{ $jenis }}">{{ $jenis }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label class="required" for="pic_user_id">Penanggung Jawab (PIC)</label>
                        <select class="form-control select2" name="pic_user_id" required>
                            @foreach($pics as $id => $entry)
                                <option value="{{ $id }}" {{ old('pic_user_id') == $id ? 'selected' : '' }}>{{ $entry }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Pilih nama pegawai yang bertanggung jawab.</small>
                    </div>

                    <div class="form-group mb-3">
                        <label class="required" for="jumlah_peserta">Estimasi Peserta</label>
                        <input class="form-control" type="number" name="jumlah_peserta" required>
                    </div>
                </div>

                {{-- KOLOM KANAN: WAKTU --}}
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="required" for="tanggal_kegiatan">Tanggal Pelaksanaan</label>
                        <input class="form-control" type="date" name="tanggal_kegiatan" required>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group mb-3">
                                <label class="required">Mulai</label>
                                <input class="form-control" type="time" name="waktu_mulai" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group mb-3">
                                <label class="required">Selesai</label>
                                <input class="form-control" type="time" name="waktu_selesai" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="lampiran">Lampiran (Undangan / Nota Dinas)</label>
                        <input type="file" class="form-control" name="lampiran" accept=".pdf,.jpg,.png">
                        <small class="text-muted">Opsional. Format: PDF/JPG. Max 5MB.</small>
                    </div>
                </div>
            </div>

            {{-- SECTION KONSUMSI (TOGGLE) --}}
            <div id="konsumsi_panel" class="card mt-3 border-warning bg-light-warning">
                <div class="card-header bg-warning text-dark fw-bold">
                    <i class="fas fa-utensils me-2"></i> Detail Permintaan Konsumsi
                </div>
                <div class="card-body bg-white">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="required">Waktu Konsumsi Datang</label>
                                <input type="time" class="form-control" name="waktu_konsumsi">
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group mb-3">
                                <label>Catatan Konsumsi (Menu / Jenis Snack)</label>
                                <textarea class="form-control" name="catatan_konsumsi" rows="2" placeholder="Contoh: 20 Snack Box (Lemper, Risol) + Teh Hangat"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer text-end mt-4">
                <a href="{{ route('admin.permintaan-kegiatan.index') }}" class="btn btn-secondary me-2">Batal</a>
                <button class="btn btn-primary" type="submit"><i class="fas fa-save me-2"></i> Ajukan Permintaan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Logic Show/Hide Konsumsi Panel
        function toggleKonsumsi() {
            if ($('#req_konsumsi').is(':checked')) {
                $('#konsumsi_panel').slideDown();
                $('input[name="waktu_konsumsi"]').prop('required', true);
            } else {
                $('#konsumsi_panel').slideUp();
                $('input[name="waktu_konsumsi"]').prop('required', false);
            }
        }

        $('#req_konsumsi').change(toggleKonsumsi);
        toggleKonsumsi(); // Run on load
    });
</script>
@endsection