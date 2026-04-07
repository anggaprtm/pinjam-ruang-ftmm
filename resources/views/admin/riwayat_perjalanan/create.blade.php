@extends('layouts.admin')

@section('styles')
<style>
    /* ----- RESPONSIVE WRAPPER ----- */
    .trip-form-wrap {
        max-width: 100%; /* Default full untuk mobile */
        margin: 0 auto;
    }

    /* Di layar Desktop (Large), kita kasih batas lebar yang lebih proporsional */
    @media (min-width: 992px) {
        .trip-form-wrap {
            max-width: 1000px; 
        }
    }

    .trip-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 24px rgba(0,0,0,.08);
        overflow: hidden;
        background: #fff;
    }

    .trip-card .card-header {
        background: linear-gradient(135deg, #741847 0%, #7e144b 100%);
        padding: 1.25rem 1.5rem;
        border: none;
    }

    .trip-card .card-header h4 {
        color: #fff;
        font-size: 1.1rem;
        font-weight: 700;
        margin: 0;
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

    /* Reset margin top untuk section pertama di tiap kolom */
    .column-left .form-section-title:first-child,
    .column-right .form-section-title:first-child { 
        margin-top: 0; 
    }

    .form-label { font-size: .82rem; font-weight: 600; color: #374151; margin-bottom: .35rem; }
    .form-label.required::after { content: ' *'; color: #ef4444; }

    .form-control, .form-select {
        border-radius: 10px;
        border: 1.5px solid #e5e7eb;
        background-color: #fafafa;
        padding: .6rem .85rem;
    }

    /* Styling khusus Card Mobil & Driver agar konsisten */
    .mobil-auto-card {
        display: flex; align-items: center; gap: .85rem;
        background: #eff6ff; border: 2px solid #bfdbfe;
        border-radius: 12px; padding: .9rem 1rem;
    }
    .driver-self-badge {
        display: flex; align-items: center; gap: .65rem;
        background: #f0fdf4; border: 1.5px solid #bbf7d0;
        border-radius: 10px; padding: .65rem 1rem;
    }

    .km-input-block {
        background: #fefce8; border: 1.5px solid #fde68a;
        border-radius: 12px; padding: 1rem;
    }

    .trip-info-alert {
        background: #eff6ff; border: 1px solid #bfdbfe;
        border-radius: 10px; padding: .75rem 1rem; font-size: .8rem;
        color: #1e40af; display: flex; gap: .5rem;
    }

    /* FOOTER FIX */
    .trip-form-footer {
        display: flex; gap: .75rem; padding: 1.25rem 1.5rem;
        background: #f9fafb; border-top: 1px solid #f3f4f6;
    }
    .btn-submit {
        flex: 2; border-radius: 10px; font-weight: 700;
        background: linear-gradient(135deg, #741847, #991e5e);
        color: #fff; border: none; padding: .75rem;
    }
    .btn-cancel {
        flex: 1; border-radius: 10px; font-weight: 600;
        background: #fff; border: 1.5px solid #e5e7eb;
        color: #6b7280; display: flex; align-items: center; justify-content: center;
    }

    @media (max-width: 575.98px) {
        .trip-card .card-body { padding: 1.15rem 1rem; }
    }
</style>
@endsection

@section('content')
<div class="trip-form-wrap">
    <div class="card trip-card">
        <div class="card-header">
            <h4><i class="fas fa-car-side me-2"></i> Input Perjalanan Dinas</h4>
        </div>

        <form method="POST" action="{{ route('admin.riwayat-perjalanan.store') }}" id="formPerjalanan">
            @csrf
            <div class="card-body">
                <div class="row">
                    {{-- === KOLOM KIRI: Subjek & Kendaraan === --}}
                    <div class="col-lg-6 column-left">
                        <div class="form-section-title">
                            <i class="fas fa-car text-primary"></i> Kendaraan & Driver
                        </div>

                        {{-- Logika Mobil --}}
                        @php $singleMobil = count($mobils) === 1 ? $mobils->first() : null; @endphp
                        @if($singleMobil)
                            <input type="hidden" name="mobil_id" value="{{ $singleMobil->id }}">
                            <div class="mobil-auto-card mb-3">
                                <div class="car-icon" style="width:40px; height:40px; background:#2563eb; color:#fff; border-radius:50%; display:flex; align-items:center; justify-content:center;">
                                    <i class="fas fa-car"></i>
                                </div>
                                <div class="car-info">
                                    <div class="fw-bold text-primary" style="font-size:.9rem;">{{ $singleMobil->nama_mobil }}</div>
                                    <div class="plate-badge" style="font-family:monospace; background:#dbeafe; padding:2px 6px; border-radius:4px; font-size:.7rem; font-weight:700;">{{ $singleMobil->plat_nomor }}</div>
                                </div>
                            </div>
                        @else
                            <div class="form-group mb-3">
                                <label class="form-label required">Pilih Kendaraan</label>
                                <select class="form-select select2" name="mobil_id" required>
                                    <option value="">-- Pilih Mobil --</option>
                                    @foreach($mobils as $mobil)
                                        <option value="{{ $mobil->id }}">{{ $mobil->nama_mobil }} ({{ $mobil->plat_nomor }})</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        {{-- Logika Driver --}}
                        @if(auth()->user()->isAdmin())
                            <div class="form-group mb-3">
                                <label class="form-label required">Nama Driver</label>
                                <select class="form-select select2" name="user_id" required>
                                    @foreach($users as $id => $entry)
                                        <option value="{{ $id }}">{{ $entry }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                            <div class="driver-self-badge mb-3">
                                <div style="width:32px; height:32px; background:#16a34a; color:#fff; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:bold;">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                                <div class="name" style="font-size:.85rem; font-weight:600; color:#15803d;">{{ auth()->user()->name }}</div>
                            </div>
                        @endif

                        <div class="form-section-title">
                            <i class="fas fa-tachometer-alt text-primary"></i> Kilometer
                        </div>

                        @if($sudahAdaKmHariIni)
                            <div class="km-already-set mb-3">
                                <i class="fas fa-check-circle text-success"></i>
                                <span class="small text-success">KM awal hari ini: <strong>{{ number_format($kmHariIni) }} km</strong></span>
                            </div>
                        @else
                            <div class="km-input-block mb-3">
                                <label class="form-label required" style="font-size:.7rem; color:#92400e;">KM ODOMETER SEKARANG</label>
                                <input class="form-control" type="number" name="km_awal" placeholder="Contoh: 45231" required>
                            </div>
                        @endif
                    </div>

                    {{-- === KOLOM KANAN: Tujuan & Waktu === --}}
                    <div class="col-lg-6 column-right">
                        <div class="form-section-title">
                            <i class="fas fa-map-marker-alt text-primary"></i> Tujuan & Waktu
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label required">Tujuan</label>
                            <input class="form-control" type="text" name="tujuan" placeholder="Lokasi tujuan..." required>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">Keperluan</label>
                            <textarea class="form-control" name="keperluan" rows="1" placeholder="Opsional..."></textarea>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label required">Waktu Berangkat</label>
                            <input class="form-control datetime" type="text" name="waktu_mulai" required>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">Estimasi Selesai</label>
                            <input class="form-control datetime" type="text" name="waktu_selesai">
                        </div>

                        <div class="trip-info-alert mt-2">
                            <i class="fas fa-info-circle"></i>
                            <span style="font-size:.75rem">Input waktu lampau/sekarang akan mengubah status mobil menjadi <strong>Dipakai</strong>.</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="trip-form-footer">
                <a href="{{ route('admin.riwayat-perjalanan.index') }}" class="btn btn-cancel">Batal</a>
                <button class="btn btn-submit" type="submit">
                    <i class="fas fa-paper-plane me-1"></i> Simpan Jadwal
                </button>
            </div>
        </form>
    </div>
</div>
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