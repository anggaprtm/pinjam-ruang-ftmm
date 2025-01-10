@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">
        Cari Ruang
    </div>

    <div class="card-body">
        <form>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <input class="form-control datetime" type="text" name="waktu_mulai" id="waktu_mulai" value="{{ request()->input('waktu_mulai') }}" placeholder="{{ trans('cruds.kegiatan.fields.waktu_mulai') }}" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <input class="form-control datetime" type="text" name="waktu_selesai" id="waktu_selesai" value="{{ request()->input('waktu_selesai') }}" placeholder="{{ trans('cruds.kegiatan.fields.waktu_selesai') }}" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <input class="form-control" type="number" name="kapasitas" id="kapasitas" value="{{ request()->input('kapasitas') }}" placeholder="Kapasitas Ruang" step="1" required>
                    </div>
                </div>
                <div class="col-md-1">
                    <button class="btn btn-success">
                        Cari
                    </button>
                </div>
            </div>
        </form>
        @if($ruangans !== null)
            <hr />
            @if($ruangans->count())
                <div class="table-responsive">
                    <table class=" table table-bordered table-striped table-hover datatable datatable-kegiatan">
                        <thead>
                            <tr>
                                <th>
                                    Ruangan Tersedia
                                </th>
                                <th>
                                    {{ trans('cruds.ruangan.fields.kapasitas') }}
                                </th>
                                <th>
                                    &nbsp;
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ruangans as $ruangan)
                                <tr>
                                    <td class="nama-ruangan">
                                        {{ $ruangan->nama ?? '' }}
                                    </td>
                                    <td>
                                        {{ $ruangan->kapasitas ?? '' }}
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#bookRuang" data-ruangan-id="{{ $ruangan->id }}">
                                            Pinjam Ruang
                                        </button>
                                    </td>

                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-center">Tidak ada ruang tersedia di waktu tersebut</p>
            @endif
        @endif
    </div>
</div>
<div class="modal" tabindex="-1" role="dialog" id="bookRuang">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pinjam Ruangan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.bookRuang') }}" method="POST" id="bookingForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="ruangan_id" id="ruangan_id" value="{{ old('ruangan_id') }}">
                    <input type="hidden" name="waktu_mulai" value="{{ request()->input('waktu_mulai') }}">
                    <input type="hidden" name="waktu_selesai" value="{{ request()->input('waktu_selesai') }}">
                    <div class="form-group">
                        <label class="required" for="nama_kegiatan">{{ trans('cruds.kegiatan.fields.nama_kegiatan') }}</label>
                        <input class="form-control {{ $errors->has('nama_kegiatan') ? 'is-invalid' : '' }}" type="text" name="nama_kegiatan" id="title" value="{{ old('nama_kegiatan', '') }}" required>
                        @if($errors->has('title'))
                            <div class="invalid-feedback">
                                {{ $errors->first('nama_kegiatan') }}
                            </div>
                        @endif
                        <span class="help-block">{{ trans('cruds.kegiatan.fields.nama_kegiatan_helper') }}</span>
                    </div>
                    <div class="form-group">
                        <label for="description">{{ trans('cruds.kegiatan.fields.deskripsi') }}</label>
                        <textarea class="form-control {{ $errors->has('description') ? 'is-invalid' : '' }}" name="description" id="description">{{ old('description') }}</textarea>
                        @if($errors->has('description'))
                            <div class="invalid-feedback">
                                {{ $errors->first('description') }}
                            </div>
                        @endif
                        <span class="help-block">{{ trans('cruds.kegiatan.fields.deskripsi_helper') }}</span>
                    </div>
                    <div class="form-group">
                        <label for="surat_izin">Upload Surat Izin (PDF)</label>
                        <input class="form-control {{ $errors->has('surat_izin') ? 'is-invalid' : '' }}" type="file" name="surat_izin" id="surat_izin" accept=".pdf" required>
                        @if($errors->has('surat_izin'))
                            <div class="invalid-feedback">
                                {{ $errors->first('surat_izin') }}
                            </div>
                        @endif
                    </div>
                    <div class="form-group" style="display: none;">
                        <label for="berulang_sampai">Berulang sampai</label>
                        <input class="form-control date {{ $errors->has('berulang_sampai') ? 'is-invalid' : '' }}" type="text" name="berulang_sampai" id="berulang_sampai" value="{{ old('berulang_sampai') }}">
                        @if($errors->has('berulang_sampai'))
                            <div class="invalid-feedback">
                                {{ $errors->first('berulang_sampai') }}
                            </div>
                        @endif
                    </div>
                    <button type="submit" style="display: none;"></button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="submitBooking">OK</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$('#bookRuang').on('show.bs.modal', function (kegiatan) {
    var button = $(kegiatan.relatedTarget);
    var ruanganId = button.data('ruangan-id');
    var modal = $(this);
    modal.find('#ruangan_id').val(ruanganId);
    modal.find('.modal-title').text('Pinjam Ruangan ' + button.parents('tr').children('.ruangan-nama').text());

    $('#submitBooking').click(function () {
        $('#bookingForm').submit(); 
        // modal.find('button[type="submit"]').trigger('click');
    });
});
</script>
@endsection