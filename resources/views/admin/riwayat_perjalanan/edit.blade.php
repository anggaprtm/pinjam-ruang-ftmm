@extends('layouts.admin')

@section('styles')
<style>
    /* ===== MOBILE-FIRST FORM CARD ===== */
    .trip-form-wrap {
        max-width: 680px;
        margin: 0 auto;
    }

    .trip-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 24px rgba(0,0,0,.08);
        overflow: hidden;
    }

    .trip-card .card-header {
        background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);
        padding: 1.25rem 1.5rem;
        border: none;
    }

    .trip-card .card-header h4 {
        color: #fff;
        font-size: 1.1rem;
        font-weight: 700;
        letter-spacing: .3px;
    }

    .trip-card .card-body {
        padding: 1.5rem;
        background: #fff;
    }

    /* ===== SECTION DIVIDER ===== */
    .form-section-title {
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: 1.2px;
        text-transform: uppercase;
        color: #6b7280;
        margin: 1.5rem 0 .75rem;
        padding-bottom: .4rem;
        border-bottom: 2px solid #f3f4f6;
        display: flex;
        align-items: center;
        gap: .5rem;
    }

    .form-section-title:first-child {
        margin-top: 0;
    }

    /* ===== FORM ELEMENTS ===== */
    .form-label {
        font-size: .82rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: .35rem;
    }

    .form-label.required::after {
        content: ' *';
        color: #ef4444;
    }

    .form-control,
    .form-select {
        border-radius: 10px;
        border: 1.5px solid #e5e7eb;
        font-size: .92rem;
        padding: .6rem .85rem;
        color: #111827;
        transition: border-color .2s, box-shadow .2s;
        background-color: #fafafa;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #d97706;
        box-shadow: 0 0 0 3px rgba(217,119,6,.12);
        background-color: #fff;
        outline: none;
    }

    .form-control.is-invalid {
        border-color: #ef4444;
        background-color: #fff8f8;
    }

    textarea.form-control {
        resize: none;
    }

    .input-group-text {
        background: #f3f4f6;
        border: 1.5px solid #e5e7eb;
        border-right: none;
        border-radius: 10px 0 0 10px;
        color: #6b7280;
        font-size: .9rem;
    }

    .input-group .form-control {
        border-left: none;
        border-radius: 0 10px 10px 0;
    }

    /* ===== SELECT2 MOBILE FIX ===== */
    .select2-container .select2-selection--single {
        height: auto !important;
        border-radius: 10px !important;
        border: 1.5px solid #e5e7eb !important;
        background: #fafafa !important;
        padding: .55rem .85rem !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 1.5 !important;
        font-size: .92rem;
        color: #111827;
        padding: 0 !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        top: 50% !important;
        transform: translateY(-50%);
        right: 10px;
    }

    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #d97706 !important;
        box-shadow: 0 0 0 3px rgba(217,119,6,.12) !important;
    }

    .select2-dropdown {
        border-radius: 10px !important;
        border: 1.5px solid #e5e7eb !important;
        box-shadow: 0 8px 24px rgba(0,0,0,.1) !important;
        font-size: .9rem;
    }

    .select2-results__option--highlighted {
        background: #d97706 !important;
    }

    /* ===== EDIT NOTICE BADGE ===== */
    .edit-notice {
        background: #fffbeb;
        border: 1px solid #fde68a;
        border-radius: 10px;
        padding: .65rem 1rem;
        font-size: .8rem;
        color: #92400e;
        display: flex;
        gap: .5rem;
        align-items: center;
        margin-bottom: 1.25rem;
    }

    /* ===== FOOTER ACTIONS ===== */
    .trip-form-footer {
        display: flex;
        gap: .75rem;
        padding: 1.25rem 1.5rem;
        background: #f9fafb;
        border-top: 1px solid #f3f4f6;
    }

    .btn-cancel {
        flex: 1;
        border-radius: 10px;
        font-weight: 600;
        font-size: .9rem;
        padding: .65rem;
        background: #fff;
        border: 1.5px solid #e5e7eb;
        color: #6b7280;
        transition: all .15s;
        text-align: center;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .btn-cancel:hover {
        border-color: #d1d5db;
        color: #374151;
        background: #f9fafb;
    }

    .btn-submit {
        flex: 2;
        border-radius: 10px;
        font-weight: 700;
        font-size: .95rem;
        padding: .65rem;
        background: linear-gradient(135deg, #b45309, #d97706);
        border: none;
        color: #fff;
        box-shadow: 0 4px 12px rgba(217,119,6,.3);
        transition: all .15s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .5rem;
    }

    .btn-submit:hover {
        background: linear-gradient(135deg, #92400e, #b45309);
        box-shadow: 0 6px 16px rgba(217,119,6,.4);
        transform: translateY(-1px);
    }

    .btn-submit:active {
        transform: translateY(0);
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 575.98px) {
        .trip-card .card-body {
            padding: 1.15rem 1rem;
        }

        .trip-form-footer {
            padding: 1rem;
        }

        .trip-card .card-header {
            padding: 1rem 1.15rem;
        }
    }
</style>
@endsection

@section('content')
<div class="trip-form-wrap">
    <div class="card trip-card">

        {{-- HEADER --}}
        <div class="card-header">
            <h4 class="mb-0">
                <i class="fas fa-edit me-2"></i> Edit Data Perjalanan
            </h4>
        </div>

        {{-- BODY --}}
        <div class="card-body">

            <div class="edit-notice">
                <i class="fas fa-exclamation-triangle"></i>
                <span>Anda sedang mengedit data perjalanan yang sudah ada. Pastikan data sudah benar sebelum menyimpan.</span>
            </div>

            <form method="POST"
                  action="{{ route('admin.riwayat-perjalanan.update', [$riwayatPerjalanan->id]) }}"
                  id="formEditPerjalanan">
                @method('PUT')
                @csrf

                {{-- === SECTION: DRIVER (ADMIN ONLY) === --}}
                @if(auth()->user()->isAdmin())
                    <div class="form-section-title">
                        <i class="fas fa-user text-warning"></i> Driver
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label required" for="user_id">Nama Driver</label>
                        <select class="form-control select2 {{ $errors->has('user_id') ? 'is-invalid' : '' }}"
                                name="user_id" id="user_id" required>
                            @foreach($users as $id => $entry)
                                <option value="{{ $id }}"
                                    {{ (old('user_id') ?? $riwayatPerjalanan->user_id) == $id ? 'selected' : '' }}>
                                    {{ $entry }}
                                </option>
                            @endforeach
                        </select>
                        @if($errors->has('user_id'))
                            <div class="invalid-feedback">{{ $errors->first('user_id') }}</div>
                        @endif
                    </div>
                @endif

                {{-- === SECTION: KENDARAAN === --}}
                <div class="form-section-title">
                    <i class="fas fa-car text-warning"></i> Kendaraan
                </div>

                <div class="form-group mb-3">
                    <label class="form-label required" for="mobil_id">Kendaraan</label>
                    <select class="form-control select2 {{ $errors->has('mobil_id') ? 'is-invalid' : '' }}"
                            name="mobil_id" id="mobil_id" required>
                        @foreach($mobils as $mobil)
                            <option value="{{ $mobil->id }}"
                                {{ (old('mobil_id') ?? $riwayatPerjalanan->mobil_id) == $mobil->id ? 'selected' : '' }}>
                                {{ $mobil->nama_mobil }} — {{ $mobil->plat_nomor }}
                            </option>
                        @endforeach
                    </select>
                    @if($errors->has('mobil_id'))
                        <div class="invalid-feedback">{{ $errors->first('mobil_id') }}</div>
                    @endif
                </div>

                {{-- === SECTION: DETAIL PERJALANAN === --}}
                <div class="form-section-title">
                    <i class="fas fa-map-marker-alt text-warning"></i> Detail Perjalanan
                </div>

                <div class="form-group mb-3">
                    <label class="form-label required" for="tujuan">Tujuan</label>
                    <input class="form-control {{ $errors->has('tujuan') ? 'is-invalid' : '' }}"
                           type="text" name="tujuan" id="tujuan"
                           value="{{ old('tujuan', $riwayatPerjalanan->tujuan) }}"
                           placeholder="Contoh: Kantor Pemkot Surabaya" required>
                    @if($errors->has('tujuan'))
                        <div class="invalid-feedback">{{ $errors->first('tujuan') }}</div>
                    @endif
                </div>

                <div class="form-group mb-3">
                    <label class="form-label" for="keperluan">Keperluan <span class="text-muted fw-normal">(opsional)</span></label>
                    <textarea class="form-control" name="keperluan" id="keperluan" rows="2"
                              placeholder="Contoh: Mengantar Dekan Rapat">{{ old('keperluan', $riwayatPerjalanan->keperluan) }}</textarea>
                </div>

                {{-- === SECTION: WAKTU === --}}
                <div class="form-section-title">
                    <i class="fas fa-clock text-warning"></i> Waktu
                </div>

                <div class="form-group mb-3">
                    <label class="form-label required" for="waktu_mulai">Waktu Berangkat</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                        <input class="form-control datetime {{ $errors->has('waktu_mulai') ? 'is-invalid' : '' }}"
                               type="text" name="waktu_mulai" id="waktu_mulai"
                               value="{{ old('waktu_mulai', $riwayatPerjalanan->waktu_mulai) }}" required>
                    </div>
                    @if($errors->has('waktu_mulai'))
                        <div class="invalid-feedback d-block">{{ $errors->first('waktu_mulai') }}</div>
                    @endif
                </div>

                <div class="form-group mb-1">
                    <label class="form-label" for="waktu_selesai">Estimasi Selesai <span class="text-muted fw-normal">(opsional)</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-calendar-check"></i></span>
                        <input class="form-control datetime" type="text" name="waktu_selesai" id="waktu_selesai"
                               value="{{ old('waktu_selesai', $riwayatPerjalanan->waktu_selesai) }}">
                    </div>
                </div>

            </form>
        </div>

        {{-- FOOTER ACTIONS --}}
        <div class="trip-form-footer">
            <a href="{{ route('admin.riwayat-perjalanan.index') }}" class="btn btn-cancel">
                Batal
            </a>
            <button class="btn btn-submit" type="submit" form="formEditPerjalanan">
                <i class="fas fa-save"></i> Simpan Perubahan
            </button>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const elMulai   = document.getElementById('waktu_mulai');
    const elSelesai = document.getElementById('waktu_selesai');

    if (elMulai && elSelesai) {
        elMulai.addEventListener('change.td', (e) => {
            let startDate = e.detail.date;
            if (!startDate) return;

            let pickerSelesai = tempusDominus.TempusDominus.getInstance(elSelesai);
            if (!pickerSelesai) return;

            pickerSelesai.updateOptions({
                restrictions: { minDate: startDate }
            });

            if (!elSelesai.value) {
                pickerSelesai.dates.setValue(startDate);
            }
        });
    }
});
</script>
@endsection