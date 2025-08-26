@extends('layouts.admin')
@section('content')

{{-- Kartu Form Pencarian --}}
<div class="card form-card search-card">
    <div class="card-header">
        <h4 class="mb-0">Cari Ruangan Tersedia</h4>
    </div>
    <div class="card-body">
        <form>
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="waktu_mulai" class="form-label fw-bold">Waktu Mulai</label>
                    <input class="form-control datetime" type="text" name="waktu_mulai" id="waktu_mulai" value="{{ request()->input('waktu_mulai') }}" required>
                </div>
                <div class="col-md-4">
                    <label for="waktu_selesai" class="form-label fw-bold">Waktu Selesai</label>
                    <input class="form-control datetime" type="text" name="waktu_selesai" id="waktu_selesai" value="{{ request()->input('waktu_selesai') }}" required>
                </div>
                <div class="col-md-2">
                    <label for="kapasitas" class="form-label fw-bold">Min. Kapasitas</label>
                    <input class="form-control" type="number" name="kapasitas" id="kapasitas" value="{{ request()->input('kapasitas') }}" placeholder="e.g. 50" step="1" required>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100" type="submit">
                        <i class="fas fa-search me-1"></i> Cari
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Hasil Pencarian --}}
@if($ruangan !== null)
<div class="card form-card mt-4">
    <div class="card-body">
        @if($ruangan->count())
            <h5 class="results-header">Ruangan Tersedia</h5>
            @foreach($ruangan as $item)
                <div class="room-card">
                    <div class="room-info">
                        <div class="icon"><i class="fas fa-door-open"></i></div>
                        <div>
                            <div class="room-name">{{ $item->nama ?? '' }}</div>
                            <div class="room-capacity">Kapasitas: {{ $item->kapasitas ?? '' }} orang</div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bookRuang" data-ruangan-id="{{ $item->id }}" data-ruangan-nama="{{ $item->nama }}">
                        Pinjam Ruang
                    </button>
                </div>
            @endforeach
        @else
            <div class="text-center py-4">
                <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Tidak ada ruangan yang tersedia</h5>
                <p>Silakan coba cari dengan waktu atau kapasitas yang berbeda.</p>
            </div>
        @endif
    </div>
</div>
@endif


{{-- Modal untuk Booking Ruangan --}}
<div class="modal fade" tabindex="-1" role="dialog" id="bookRuang">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pinjam Ruangan: <span id="modal-ruangan-nama"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.bookRuang') }}" method="POST" id="bookingForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="ruangan_id" id="modal_ruangan_id">
                    <input type="hidden" name="waktu_mulai" value="{{ request()->input('waktu_mulai') }}">
                    <input type="hidden" name="waktu_selesai" value="{{ request()->input('waktu_selesai') }}">

                    <div class="form-group mb-3">
                        <label class="form-label required" for="nama_kegiatan">{{ trans('cruds.kegiatan.fields.nama_kegiatan') }}</label>
                        <input class="form-control {{ $errors->has('nama_kegiatan') ? 'is-invalid' : '' }}" type="text" name="nama_kegiatan" id="nama_kegiatan" value="{{ old('nama_kegiatan', '') }}" required>
                        @if($errors->has('nama_kegiatan'))
                            <div class="invalid-feedback">{{ $errors->first('nama_kegiatan') }}</div>
                        @endif
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label" for="description">{{ trans('cruds.kegiatan.fields.deskripsi') }}</label>
                        <textarea class="form-control {{ $errors->has('description') ? 'is-invalid' : '' }}" name="description" id="description">{{ old('description') }}</textarea>
                        @if($errors->has('description'))
                            <div class="invalid-feedback">{{ $errors->first('description') }}</div>
                        @endif
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label" for="nomor_telepon">Nomor Telepon PIC</label>
                        <input type="text" name="nomor_telepon" id="nomor_telepon" class="form-control {{ $errors->has('nomor_telepon') ? 'is-invalid' : '' }}" value="{{ old('nomor_telepon') }}">
                        @if($errors->has('nomor_telepon'))
                            <div class="invalid-feedback">{{ $errors->first('nomor_telepon') }}</div>
                        @endif
                    </div>
                    <div class="form-group mb-3">
                        <label for="surat_izin">Upload Surat Izin (PDF)</label>
                        <input class="form-control {{ $errors->has('surat_izin') ? 'is-invalid' : '' }}" type="file" name="surat_izin" id="surat_izin" accept=".pdf">
                        @if($errors->has('surat_izin'))
                            <div class="invalid-feedback">{{ $errors->first('surat_izin') }}</div>
                        @endif
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="submitBooking">OK</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@parent
<script>
document.addEventListener('DOMContentLoaded', function () {
    const bookRuangModal = document.getElementById('bookRuang');
    if (bookRuangModal) {
        bookRuangModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const ruanganId = button.getAttribute('data-ruangan-id');
            const ruanganNama = button.getAttribute('data-ruangan-nama');

            const modalTitle = bookRuangModal.querySelector('#modal-ruangan-nama');
            const modalRuanganIdInput = bookRuangModal.querySelector('#modal_ruangan_id');

            modalTitle.textContent = ruanganNama;
            modalRuanganIdInput.value = ruanganId;
        });
    }

    const submitBookingButton = document.getElementById('submitBooking');
    if (submitBookingButton) {
        submitBookingButton.addEventListener('click', function () {
            document.getElementById('bookingForm').submit();
        });
    }
});
</script>
@endsection
