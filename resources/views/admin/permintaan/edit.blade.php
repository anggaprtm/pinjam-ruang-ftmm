@extends('layouts.admin')
@section('content')

<div class="card form-card">
    <div class="card-header bg-warning text-dark">
        <h4 class="mb-0"><i class="fas fa-edit me-2"></i> Edit Permintaan Layanan</h4>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.permintaan-kegiatan.update", [$permintaan->id]) }}" enctype="multipart/form-data">
            @method('PUT')
            @csrf
            
            {{-- INFO STATUS --}}
            <div class="alert alert-warning mb-4">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Anda sedang mengedit permintaan. Pastikan data sudah benar sebelum disimpan.
            </div>

            <div class="alert alert-info bg-light-info border-0 mb-4">
                <label class="fw-bold mb-2">Jenis Layanan:</label>
                <div class="d-flex gap-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="request_ruang" id="req_ruang" {{ $permintaan->request_ruang ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold" for="req_ruang">Peminjaman Ruang</label>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="request_konsumsi" id="req_konsumsi" {{ $permintaan->request_konsumsi ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold" for="req_konsumsi">Permintaan Konsumsi</label>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="required" for="nama_kegiatan">Nama Kegiatan</label>
                        <input class="form-control" type="text" name="nama_kegiatan" value="{{ old('nama_kegiatan', $permintaan->nama_kegiatan) }}" required>
                    </div>

                    <div class="form-group mb-3">
                        <label class="required" for="jenis_kegiatan">Jenis Kegiatan</label>
                        <select class="form-control select2" name="jenis_kegiatan" required>
                            @foreach(['Rapat', 'Seminar Proposal', 'Sidang Skripsi', 'Kegiatan Ormawa', 'Lainnya'] as $jenis)
                                <option value="{{ $jenis }}" {{ (old('jenis_kegiatan') ? old('jenis_kegiatan') : $permintaan->jenis_kegiatan) == $jenis ? 'selected' : '' }}>{{ $jenis }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label class="required" for="pic_user_id">Penanggung Jawab (PIC)</label>
                        <select class="form-control select2" name="pic_user_id" required>
                            @foreach($pics as $id => $entry)
                                <option value="{{ $id }}" {{ (old('pic_user_id') ? old('pic_user_id') : $permintaan->pic_user_id) == $id ? 'selected' : '' }}>{{ $entry }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label class="required" for="jumlah_peserta">Estimasi Peserta</label>
                        <input class="form-control" type="number" name="jumlah_peserta" value="{{ old('jumlah_peserta', $permintaan->jumlah_peserta) }}" required>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="required" for="tanggal_kegiatan">Tanggal Pelaksanaan</label>
                        <input class="form-control" type="date" name="tanggal_kegiatan" value="{{ old('tanggal_kegiatan', $permintaan->tanggal_kegiatan->format('Y-m-d')) }}" required>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group mb-3">
                                <label class="required">Mulai</label>
                                <input class="form-control" type="time" name="waktu_mulai" value="{{ old('waktu_mulai', \Carbon\Carbon::parse($permintaan->waktu_mulai)->format('H:i')) }}" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group mb-3">
                                <label class="required">Selesai</label>
                                <input class="form-control" type="time" name="waktu_selesai" value="{{ old('waktu_selesai', \Carbon\Carbon::parse($permintaan->waktu_selesai)->format('H:i')) }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="lampiran">Ganti Lampiran (Opsional)</label>
                        <input type="file" class="form-control" name="lampiran" accept=".pdf,.jpg,.png">
                        @if($permintaan->lampiran)
                            <small class="text-muted">File saat ini: <a href="{{ asset('storage/'.$permintaan->lampiran) }}" target="_blank">Lihat File</a></small>
                        @endif
                    </div>
                </div>
            </div>

            <div id="konsumsi_panel" class="card mt-3 border-warning bg-light-warning" style="display: none;">
                <div class="card-header bg-warning text-dark fw-bold">
                    <i class="fas fa-utensils me-2"></i> Detail Permintaan Konsumsi
                </div>
                <div class="card-body bg-white">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="required">Waktu Konsumsi Datang</label>
                                <input type="time" class="form-control" name="waktu_konsumsi" value="{{ old('waktu_konsumsi', $permintaan->waktu_konsumsi ? \Carbon\Carbon::parse($permintaan->waktu_konsumsi)->format('H:i') : '') }}">
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group mb-3">
                                <label>Catatan Konsumsi</label>
                                <textarea class="form-control" name="catatan_konsumsi" rows="2">{{ old('catatan_konsumsi', $permintaan->catatan_konsumsi) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer text-end mt-4">
                <a href="{{ route('admin.permintaan-kegiatan.show', $permintaan->id) }}" class="btn btn-secondary me-2">Batal</a>
                <button class="btn btn-primary" type="submit"><i class="fas fa-save me-2"></i> Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
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
        toggleKonsumsi(); // Init state
    });
</script>
@endsection