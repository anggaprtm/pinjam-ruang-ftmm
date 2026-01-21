@extends('layouts.admin')
@section('content')

<div class="card form-card">
    <div class="card-header">
        <h4 class="mb-0"><i class="fas fa-car-side me-2"></i> Input Perjalanan Dinas / Booking</h4>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.riwayat-perjalanan.store") }}">
            @csrf
            <div class="row">
                {{-- Kolom Kiri: Detail Kendaraan --}}
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label required" for="mobil_id">Pilih Kendaraan</label>
                        <select class="form-control select2 {{ $errors->has('mobil_id') ? 'is-invalid' : '' }}" name="mobil_id" id="mobil_id" required>
                            <option value="">-- Pilih Mobil --</option>
                            @foreach($mobils as $mobil)
                                <option value="{{ $mobil->id }}" {{ old('mobil_id') == $mobil->id ? 'selected' : '' }}>
                                    {{ $mobil->nama_mobil }} - {{ $mobil->plat_nomor }} 
                                    ({{ $mobil->status == 'tersedia' ? '🟢 Ready' : '🔴 Dipakai' }})
                                </option>
                            @endforeach
                        </select>
                        @if($errors->has('mobil_id'))
                            <div class="invalid-feedback">{{ $errors->first('mobil_id') }}</div>
                        @endif
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label required" for="tujuan">Tujuan</label>
                        <input class="form-control" type="text" name="tujuan" id="tujuan" placeholder="Contoh: Kantor Pemkot Surabaya" required>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label" for="keperluan">Keperluan</label>
                        <textarea class="form-control" name="keperluan" id="keperluan" rows="2" placeholder="Contoh: Mengantar Dekan Rapat"></textarea>
                    </div>
                    @if(auth()->user()->isAdmin())
                        <div class="form-group mb-3">
                            <label class="form-label required" for="user_id">Nama Driver</label>
                            <select class="form-control select2 {{ $errors->has('user_id') ? 'is-invalid' : '' }}" name="user_id" id="user_id" required>
                                @foreach($users as $id => $entry)
                                    <option value="{{ $id }}" {{ old('user_id') == $id ? 'selected' : '' }}>{{ $entry }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Sebagai Admin, Anda bisa memilihkan driver.</small>
                        </div>
                    @else
                        {{-- Kalau Driver, hidden input ID dia sendiri --}}
                        <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                    @endif
                </div>

                {{-- Kolom Kanan: Waktu --}}
                <div class="col-md-6">
                    <div class="alert alert-info">
                        <small><i class="fas fa-info-circle"></i> Jika waktu mulai diisi <strong>Sekarang</strong>, status mobil otomatis menjadi "Dipakai". Jika masa depan, akan masuk "Jadwal Mendatang".</small>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label required" for="waktu_mulai">Waktu Berangkat</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                            <input class="form-control datetime" type="text" name="waktu_mulai" id="waktu_mulai" required>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label" for="waktu_selesai">Estimasi Selesai (Opsional)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-calendar-check"></i></span>
                            <input class="form-control datetime" type="text" name="waktu_selesai" id="waktu_selesai">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer text-end">
                <a href="{{ route('admin.riwayat-perjalanan.index') }}" class="btn btn-secondary me-2">Batal</a>
                <button class="btn btn-primary" type="submit"><i class="fas fa-paper-plane me-2"></i> Simpan Jadwal</button>
            </div>
        </form>
    </div>
</div>
@endsection