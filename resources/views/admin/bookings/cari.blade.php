@extends('layouts.admin')

@section('content')
{{-- === FORM PENCARIAN === --}}
<div class="card shadow-sm"> 
    <div class="card-header"> 
        <h4 class="mb-0">Cari Ruangan Tersedia</h4> 
    </div> <div class="card-body"> 
        <form action="{{ route('admin.cariRuang') }}" method="GET"> 
            <div class="row g-3 align-items-end"> 
                <div class="col-md-4"> 
                    <label for="waktu_mulai" class="form-label fw-bold required">Waktu Mulai</label>
                    <div class="input-group">
                        <span class="input-group-text" id="waktu_mulai_toggle" role="button" data-bs-toggle="tooltip" title="Buka picker (Waktu Mulai)" aria-label="Buka picker waktu mulai"><i class="fas fa-calendar-alt"></i></span>
                        <input class="form-control datetime" type="text" name="waktu_mulai" id="waktu_mulai" value="{{ request()->input('waktu_mulai') }}" required>
                    </div>
                </div> 
                <div class="col-md-4"> 
                    <label for="waktu_selesai" class="form-label fw-bold required">Waktu Selesai</label>
                    <div class="input-group">
                        <span class="input-group-text" id="waktu_selesai_toggle" role="button" data-bs-toggle="tooltip" title="Buka picker (Waktu Selesai)" aria-label="Buka picker waktu selesai"><i class="fas fa-calendar-alt"></i></span>
                        <input class="form-control datetime" type="text" name="waktu_selesai" id="waktu_selesai" value="{{ request()->input('waktu_selesai') }}" required>
                    </div>
                </div> 
                <div class="col-md-2"> <label for="kapasitas" class="form-label fw-bold required">Min. Kapasitas</label> 
                    <input class="form-control" type="number" name="kapasitas" id="kapasitas" value="{{ request()->input('kapasitas') }}" placeholder="cth. 50" step="1" required> 
                </div> 
                <div class="col-md-2"> 
                    <div class="d-flex"> 
                    <button class="btn btn-primary w-100 me-2" type="submit"> 
                        <i class="fas fa-search me-1"></i> Cari </button> 
                        <a href="{{ route('admin.cariRuang') }}" class="btn btn-secondary" title="Reset Pencarian"> 
                            <i class="fas fa-sync-alt"></i> 
                        </a> 
                    </div> 
                </div> 
            </div> 
        </form> 
    </div> 
</div>

{{-- === DAFTAR RUANG === --}}
<div class="mt-4">
    <h4 class="fw-bold mb-3">
        @if(request()->filled('waktu_mulai'))
            Ruangan Tersedia ({{ $ruangan->count() }} ditemukan)
        @else
            Daftar Semua Ruangan
        @endif
    </h4>

    <div class="row">
        @forelse ($ruangan as $item)
            <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                <div class="card room-card shadow-sm border-0 text-center h-100">
                    <img src="{{ $item->foto ? asset('storage/' . $item->foto) : asset('assets/img/unsplash/ruangan_default.jpg') }}"
                         class="card-img-top" alt="{{ $item->nama }}">
                    <div class="card-body">
                        <h5 class="fw-semibold mb-2 text-dark">{{ $item->nama }}</h5>

                        <div class="d-flex justify-content-center gap-2 mb-3">
                            <span class="badge bg-dark d-flex align-items-center">
                                <i class="fas fa-users me-1"></i> {{ $item->kapasitas }} Orang
                            </span>
                            <span class="badge bg-secondary d-flex align-items-center text-dark">
                                <i class="fas fa-building me-1"></i> Lantai {{ $item->lantai }}
                            </span>
                        </div>

                        <button type="button" 
                                class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2"
                                data-bs-toggle="modal"
                                data-bs-target="#bookRuang"
                                data-ruangan-id="{{ $item->id }}"
                                data-ruangan-nama="{{ $item->nama }}"
                                {{ !request()->filled('waktu_mulai') ? 'disabled' : '' }}>
                            <i class="fas fa-calendar-check"></i> Pinjam Ruang
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-warning text-center">
                    <h4 class="alert-heading mb-2">Tidak Ada Ruangan Ditemukan</h4>
                    <p class="mb-0">
                        @if(request()->filled('waktu_mulai'))
                            Tidak ada ruangan yang tersedia sesuai kriteria. Coba ubah waktu atau kapasitas.
                        @else
                            Belum ada ruangan terdaftar atau aktif di sistem.
                        @endif
                    </p>
                </div>
            </div>
        @endforelse
    </div>
    @if(!request()->filled('waktu_mulai'))
        <div class="alert alert-info mt-3">
            <i class="fas fa-info-circle me-2"></i>
            Silakan isi <strong>Waktu Mulai</strong>, <strong>Waktu Selesai</strong>, dan <strong>Kapasitas</strong> terlebih dahulu untuk meminjam ruangan.
        </div>
    @endif
</div>

{{-- === MODAL BOOKING RUANG === --}}
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
                        <label class="form-label required" for="nama_pic">Nama PIC</label>
                        <input type="text"
                            name="nama_pic"
                            id="nama_pic"
                            class="form-control {{ $errors->has('nama_pic') ? 'is-invalid' : '' }}"
                            value="{{ old('nama_pic') }}"
                            required>
                        @if($errors->has('nama_pic'))
                            <div class="invalid-feedback">{{ $errors->first('nama_pic') }}</div>
                        @endif
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label required" for="nomor_telepon">Nomor Telepon PIC</label>
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

    // Toggle datetimepicker when calendar icon clicked (search form)
    const waktuMulaiToggle = document.getElementById('waktu_mulai_toggle');
    if (waktuMulaiToggle) {
        waktuMulaiToggle.addEventListener('click', function (e) {
            e.preventDefault();
            try {
                $('#waktu_mulai').data('DateTimePicker').show();
            } catch (err) {
                document.getElementById('waktu_mulai').focus();
            }
        });
    }

    const waktuSelesaiToggle = document.getElementById('waktu_selesai_toggle');
    if (waktuSelesaiToggle) {
        waktuSelesaiToggle.addEventListener('click', function (e) {
            e.preventDefault();
            try {
                $('#waktu_selesai').data('DateTimePicker').show();
            } catch (err) {
                document.getElementById('waktu_selesai').focus();
            }
        });
    }
});
</script>
@endsection
@section('styles')
<style>
.room-card {
    border-radius: 12px;
    overflow: hidden;
    background-color: #fff;
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}
.room-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
}
.room-card .card-img-top {
    height: 180px;
    width: 100%;
    object-fit: cover;
}
.room-card .card-body {
    padding: 1rem 1rem 1.25rem;
} 
.room-card h5 {
    font-size: 1.1rem;
}
.room-card .badge {
    font-size: 0.85rem;
    padding: 0.5em 0.75em;
    border-radius: 8px;
}
.room-card .btn-success {
    border-radius: 10px;
    font-weight: 500;
    background-color: #34c759;
    border: none;
}
.room-card .btn-success:hover {
    background-color: #2db14d;
}
</style>
@endsection
