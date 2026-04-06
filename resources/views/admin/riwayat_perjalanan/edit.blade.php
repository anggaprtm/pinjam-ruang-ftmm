@extends('layouts.admin')
@section('content')

<div class="card form-card">
    <div class="card-header">
        <h4 class="mb-0">Edit Data Perjalanan</h4>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.riwayat-perjalanan.update", [$riwayatPerjalanan->id]) }}">
            @method('PUT')
            @csrf
            
            <div class="row">
                <div class="col-md-6">
                    {{-- PILIH DRIVER (ADMIN ONLY) --}}
                    @if(auth()->user()->isAdmin())
                        <div class="form-group mb-3">
                            <label class="form-label required" for="user_id">Nama Driver</label>
                            <select class="form-control select2" name="user_id" id="user_id" required>
                                @foreach($users as $id => $entry)
                                    <option value="{{ $id }}" {{ (old('user_id') ? old('user_id') : $riwayatPerjalanan->user_id) == $id ? 'selected' : '' }}>{{ $entry }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="form-group mb-3">
                        <label class="form-label required" for="mobil_id">Kendaraan</label>
                        <select class="form-control select2" name="mobil_id" id="mobil_id" required>
                            @foreach($mobils as $mobil)
                                <option value="{{ $mobil->id }}" {{ (old('mobil_id') ? old('mobil_id') : $riwayatPerjalanan->mobil_id) == $mobil->id ? 'selected' : '' }}>
                                    {{ $mobil->nama_mobil }} - {{ $mobil->plat_nomor }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label required" for="tujuan">Tujuan</label>
                        <input class="form-control" type="text" name="tujuan" value="{{ old('tujuan', $riwayatPerjalanan->tujuan) }}" required>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label required" for="waktu_mulai">Waktu Berangkat</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                            <input class="form-control datetime" type="text" name="waktu_mulai" value="{{ old('waktu_mulai', $riwayatPerjalanan->waktu_mulai) }}" required>
                        </div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label" for="keperluan">Keperluan</label>
                        <textarea class="form-control" name="keperluan" rows="2">{{ old('keperluan', $riwayatPerjalanan->keperluan) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="card-footer text-end">
                <a href="{{ route('admin.riwayat-perjalanan.index') }}" class="btn btn-secondary me-2">Batal</a>
                <button class="btn btn-primary" type="submit">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const elMulai = document.getElementById('waktu_mulai');
    const elSelesai = document.getElementById('waktu_selesai');

    if (elMulai && elSelesai) {
        // Event listener bawaan Tempus Dominus saat tanggal berubah
        elMulai.addEventListener('change.td', (e) => {
            let startDate = e.detail.date; // Object waktu yang baru dipilih
            
            if (startDate) {
                // Ambil instance picker waktu_selesai
                let pickerSelesai = tempusDominus.TempusDominus.getInstance(elSelesai);
                
                if (pickerSelesai) {
                    // 1. Kunci tanggal & jam sebelum waktu_mulai agar tidak bisa dipilih
                    pickerSelesai.updateOptions({
                        restrictions: {
                            minDate: startDate
                        }
                    });

                    // 2. Otomatis set nilai waktu_selesai sama dengan waktu_mulai JIKA masih kosong
                    if (!elSelesai.value) {
                        // Tambahkan estimasi misal +2 jam secara otomatis (opsional)
                        // startDate.setHours(startDate.getHours() + 2); 
                        pickerSelesai.dates.setValue(startDate);
                    }
                }
            }
        });
    }
});
</script>
@endsection
@endsection