@extends('layouts.admin')

@section('styles')
<style>
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
        background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%);
        padding: 1.25rem 1.5rem;
        border: none;
    }

    .trip-card .card-header h4 {
        color: #fff;
        font-size: 1.1rem;
        font-weight: 700;
    }

    .trip-card .card-body {
        padding: 1.5rem;
        background: #fff;
    }

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

    .form-section-title:first-child { margin-top: 0; }

    .form-label {
        font-size: .82rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: .35rem;
    }

    .form-label.required::after { content: ' *'; color: #ef4444; }

    .form-control, .form-select {
        border-radius: 10px;
        border: 1.5px solid #e5e7eb;
        font-size: .92rem;
        padding: .6rem .85rem;
        color: #111827;
        transition: border-color .2s, box-shadow .2s;
        background-color: #fafafa;
    }

    .form-control:focus, .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37,99,235,.12);
        background-color: #fff;
        outline: none;
    }

    .form-control.is-invalid { border-color: #ef4444; }

    textarea.form-control { resize: none; }

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

    /* ----- MOBIL CARD (auto-select, 1 mobil) ----- */
    .mobil-auto-card {
        display: flex;
        align-items: center;
        gap: .85rem;
        background: #eff6ff;
        border: 2px solid #bfdbfe;
        border-radius: 12px;
        padding: .9rem 1rem;
    }

    .mobil-auto-card .car-icon {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: #2563eb;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .mobil-auto-card .car-info .car-name {
        font-weight: 700;
        font-size: .95rem;
        color: #1e40af;
    }

    .mobil-auto-card .car-info .car-plate {
        display: inline-block;
        background: #dbeafe;
        border-radius: 4px;
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .5px;
        color: #1d4ed8;
        padding: .08rem .4rem;
        font-family: monospace;
        margin-top: .1rem;
    }

    .mobil-auto-card .car-status {
        margin-left: auto;
        font-size: .72rem;
        font-weight: 700;
        padding: .2rem .5rem;
        border-radius: 6px;
        background: #dcfce7;
        color: #15803d;
    }

    .mobil-auto-card .car-status.busy {
        background: #fee2e2;
        color: #b91c1c;
    }

    /* ----- DRIVER BADGE ----- */
    .driver-self-badge {
        display: flex;
        align-items: center;
        gap: .65rem;
        background: #f0fdf4;
        border: 1.5px solid #bbf7d0;
        border-radius: 10px;
        padding: .65rem 1rem;
    }

    .driver-self-badge .avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #16a34a;
        color: #fff;
        font-weight: 700;
        font-size: .95rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .driver-self-badge .name { font-weight: 600; font-size: .9rem; color: #15803d; }
    .driver-self-badge .label { font-size: .75rem; color: #6b7280; }

    /* Select2 */
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
        border-color: #2563eb !important;
        box-shadow: 0 0 0 3px rgba(37,99,235,.12) !important;
    }

    .select2-dropdown {
        border-radius: 10px !important;
        border: 1.5px solid #e5e7eb !important;
        box-shadow: 0 8px 24px rgba(0,0,0,.1) !important;
        font-size: .9rem;
    }

    /* ----- KM INPUT BLOCK ----- */
    .km-input-block {
        background: #fefce8;
        border: 1.5px solid #fde68a;
        border-radius: 12px;
        padding: 1rem;
        margin-top: .25rem;
    }

    .km-input-block .km-header {
        font-size: .75rem;
        font-weight: 700;
        letter-spacing: .8px;
        text-transform: uppercase;
        color: #92400e;
        margin-bottom: .65rem;
        display: flex;
        align-items: center;
        gap: .4rem;
    }

    .km-input-block .form-control {
        border-color: #fde68a;
        font-size: 1.05rem;
        font-weight: 700;
        text-align: center;
    }

    .km-input-block .form-control:focus {
        border-color: #f59e0b;
        box-shadow: 0 0 0 3px rgba(245,158,11,.15);
    }

    .km-already-set {
        background: #f0fdf4;
        border: 1.5px solid #bbf7d0;
        border-radius: 12px;
        padding: .85rem 1rem;
        font-size: .85rem;
        color: #15803d;
        display: flex;
        align-items: center;
        gap: .5rem;
    }

    /* ----- INFO ALERT ----- */
    .trip-info-alert {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 10px;
        padding: .75rem 1rem;
        font-size: .8rem;
        color: #1e40af;
        display: flex;
        gap: .5rem;
        align-items: flex-start;
        line-height: 1.5;
    }

    .trip-info-alert i { margin-top: .05rem; flex-shrink: 0; }

    /* ----- FOOTER ----- */
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
        text-align: center;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all .15s;
    }

    .btn-cancel:hover { border-color: #d1d5db; color: #374151; }

    .btn-submit {
        flex: 2;
        border-radius: 10px;
        font-weight: 700;
        font-size: .95rem;
        padding: .65rem;
        background: linear-gradient(135deg, #1d4ed8, #2563eb);
        border: none;
        color: #fff;
        box-shadow: 0 4px 12px rgba(37,99,235,.3);
        transition: all .15s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .5rem;
    }

    .btn-submit:hover { background: linear-gradient(135deg,#1e40af,#1d4ed8); transform: translateY(-1px); }
    .btn-submit:active { transform: translateY(0); }

    @media (max-width: 575.98px) {
        .trip-card .card-body { padding: 1.15rem 1rem; }
        .trip-form-footer { padding: 1rem; }
        .trip-card .card-header { padding: 1rem 1.15rem; }
    }
</style>
@endsection

@section('content')
<div class="trip-form-wrap">
    <div class="card trip-card">

        <div class="card-header">
            <h4 class="mb-0"><i class="fas fa-car-side me-2"></i> Input Perjalanan Dinas</h4>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('admin.riwayat-perjalanan.store') }}" id="formPerjalanan">
                @csrf

                {{-- === KENDARAAN === --}}
                <div class="form-section-title">
                    <i class="fas fa-car text-primary"></i> Kendaraan
                </div>

                @php $singleMobil = count($mobils) === 1 ? $mobils->first() : null; @endphp

                @if($singleMobil)
                    {{-- Auto-select: hanya 1 kendaraan, tampilkan sebagai card --}}
                    <input type="hidden" name="mobil_id" value="{{ $singleMobil->id }}">
                    <div class="mobil-auto-card mb-3">
                        <div class="car-icon"><i class="fas fa-car"></i></div>
                        <div class="car-info">
                            <div class="car-name">{{ $singleMobil->nama_mobil }}</div>
                            <div class="car-plate">{{ $singleMobil->plat_nomor }}</div>
                        </div>
                        <div class="car-status {{ $singleMobil->status != 'tersedia' ? 'busy' : '' }}">
                            {{ $singleMobil->status == 'tersedia' ? '🟢 Ready' : '🔴 Dipakai' }}
                        </div>
                    </div>
                @else
                    {{-- Multiple mobil: pakai select --}}
                    <div class="form-group mb-3">
                        <label class="form-label required" for="mobil_id">Pilih Kendaraan</label>
                        <select class="form-control select2 {{ $errors->has('mobil_id') ? 'is-invalid' : '' }}"
                                name="mobil_id" id="mobil_id" required>
                            <option value="">-- Pilih Mobil --</option>
                            @foreach($mobils as $mobil)
                                <option value="{{ $mobil->id }}" {{ old('mobil_id') == $mobil->id ? 'selected' : '' }}>
                                    {{ $mobil->nama_mobil }} — {{ $mobil->plat_nomor }}
                                    ({{ $mobil->status == 'tersedia' ? '🟢 Ready' : '🔴 Dipakai' }})
                                </option>
                            @endforeach
                        </select>
                        @if($errors->has('mobil_id'))
                            <div class="invalid-feedback">{{ $errors->first('mobil_id') }}</div>
                        @endif
                    </div>
                @endif

                {{-- === DRIVER === --}}
                <div class="form-section-title">
                    <i class="fas fa-user text-primary"></i> Driver
                </div>

                @if(auth()->user()->isAdmin())
                    <div class="form-group mb-3">
                        <label class="form-label required" for="user_id">Nama Driver</label>
                        <select class="form-control select2 {{ $errors->has('user_id') ? 'is-invalid' : '' }}"
                                name="user_id" id="user_id" required>
                            @foreach($users as $id => $entry)
                                <option value="{{ $id }}" {{ old('user_id') == $id ? 'selected' : '' }}>{{ $entry }}</option>
                            @endforeach
                        </select>
                        @if($errors->has('user_id'))
                            <div class="invalid-feedback">{{ $errors->first('user_id') }}</div>
                        @endif
                        <small class="text-muted mt-1 d-block">
                            <i class="fas fa-shield-alt me-1"></i> Admin dapat memilihkan driver.
                        </small>
                    </div>
                @else
                    <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                    <div class="driver-self-badge mb-3">
                        <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                        <div>
                            <div class="name">{{ auth()->user()->name }}</div>
                            <div class="label">Anda tercatat sebagai driver perjalanan ini</div>
                        </div>
                    </div>
                @endif

                {{-- === DETAIL PERJALANAN === --}}
                <div class="form-section-title">
                    <i class="fas fa-map-marker-alt text-primary"></i> Detail Perjalanan
                </div>

                <div class="form-group mb-3">
                    <label class="form-label required" for="tujuan">Tujuan</label>
                    <input class="form-control {{ $errors->has('tujuan') ? 'is-invalid' : '' }}"
                           type="text" name="tujuan" id="tujuan"
                           value="{{ old('tujuan') }}"
                           placeholder="Contoh: Kantor Pemkot Surabaya" required>
                    @if($errors->has('tujuan'))
                        <div class="invalid-feedback">{{ $errors->first('tujuan') }}</div>
                    @endif
                </div>

                <div class="form-group mb-3">
                    <label class="form-label" for="keperluan">
                        Keperluan <span class="text-muted fw-normal">(opsional)</span>
                    </label>
                    <textarea class="form-control" name="keperluan" id="keperluan"
                              rows="2" placeholder="Contoh: Mengantar Dekan Rapat">{{ old('keperluan') }}</textarea>
                </div>

                {{-- === KILOMETER === --}}
                <div class="form-section-title">
                    <i class="fas fa-tachometer-alt text-primary"></i> Kilometer Kendaraan
                </div>

                @if($sudahAdaKmHariIni)
                    {{-- KM sudah diinput trip pertama hari ini --}}
                    <div class="km-already-set mb-3">
                        <i class="fas fa-check-circle"></i>
                        <div>
                            <strong>KM awal hari ini sudah tercatat:</strong> {{ number_format($kmHariIni) }} km
                            <div style="font-size:.75rem; opacity:.8;">Diinput pada perjalanan pertama hari ini. Tidak perlu diisi ulang.</div>
                        </div>
                    </div>
                @else
                    {{-- Belum ada KM hari ini → wajib input --}}
                    <div class="km-input-block mb-3">
                        <div class="km-header">
                            <i class="fas fa-tachometer-alt"></i> Input KM Odometer Sekarang
                        </div>
                        <div class="form-group">
                            <input class="form-control {{ $errors->has('km_awal') ? 'is-invalid' : '' }}"
                                   type="number" name="km_awal" id="km_awal"
                                   value="{{ old('km_awal') }}"
                                   placeholder="Contoh: 45231"
                                   min="0" step="1">
                            @if($errors->has('km_awal'))
                                <div class="invalid-feedback">{{ $errors->first('km_awal') }}</div>
                            @endif
                            <div class="mt-2" style="font-size:.75rem; color:#92400e;">
                                <i class="fas fa-info-circle me-1"></i>
                                  KM ini hanya diinput sekali di awal hari.
                            </div>
                        </div>
                    </div>
                @endif

                {{-- === WAKTU === --}}
                <div class="form-section-title">
                    <i class="fas fa-clock text-primary"></i> Waktu
                </div>

                <div class="trip-info-alert mb-3">
                    <i class="fas fa-info-circle"></i>
                    <span>
                        Waktu mulai <strong>sekarang atau lampau</strong> → status mobil otomatis <strong>Dipakai</strong>.
                        Waktu mendatang → masuk <strong>Jadwal</strong>.
                    </span>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label required" for="waktu_mulai">Waktu Berangkat</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                        <input class="form-control datetime {{ $errors->has('waktu_mulai') ? 'is-invalid' : '' }}"
                               type="text" name="waktu_mulai" id="waktu_mulai" required>
                    </div>
                    @if($errors->has('waktu_mulai'))
                        <div class="invalid-feedback d-block">{{ $errors->first('waktu_mulai') }}</div>
                    @endif
                </div>

                <div class="form-group mb-1">
                    <label class="form-label" for="waktu_selesai">
                        Estimasi Selesai <span class="text-muted fw-normal">(opsional)</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-calendar-check"></i></span>
                        <input class="form-control datetime" type="text" name="waktu_selesai" id="waktu_selesai">
                    </div>
                </div>

            </form>
        </div>

        <div class="trip-form-footer">
            <a href="{{ route('admin.riwayat-perjalanan.index') }}" class="btn btn-cancel">Batal</a>
            <button class="btn btn-submit" type="submit" form="formPerjalanan">
                <i class="fas fa-paper-plane"></i> Simpan Jadwal
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

            pickerSelesai.updateOptions({ restrictions: { minDate: startDate } });
            if (!elSelesai.value) pickerSelesai.dates.setValue(startDate);
        });
    }
});
</script>
@endsection