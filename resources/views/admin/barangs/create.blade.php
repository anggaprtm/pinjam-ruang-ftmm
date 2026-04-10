@extends('layouts.admin')
@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-plus-circle me-2 text-success"></i>Tambah Master Barang (Peminjaman)</h5>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.barangs.store") }}" enctype="multipart/form-data">
            @csrf
            
            <div class="alert alert-info small mb-4">
                <i class="fas fa-info-circle me-1"></i> Data barang diambil dari <strong>Aset Fakultas</strong> yang kondisinya <strong>Baik</strong>. Jika barang yang Anda cari tidak ada, pastikan barang tersebut sudah diinput di menu Aset Fakultas.
            </div>

            <div class="form-group">
                <label class="required" for="nama_barang">Pilih Barang dari Aset</label>
                <select class="form-control select2 {{ $errors->has('nama_barang') ? 'is-invalid' : '' }}" name="nama_barang" id="nama_barang" required>
                    <option value="">-- Cari dan Pilih Barang --</option>
                    @foreach($asetTersedia as $namaLengkap => $jumlah)
                        <option value="{{ $namaLengkap }}" data-stok="{{ $jumlah }}" {{ old('nama_barang') == $namaLengkap ? 'selected' : '' }}>
                            {{ $namaLengkap }} (Tersedia di Aset: {{ $jumlah }} unit)
                        </option>
                    @endforeach
                </select>
                @if($errors->has('nama_barang'))
                    <div class="invalid-feedback">{{ $errors->first('nama_barang') }}</div>
                @endif
            </div>

            <div class="form-group">
                <label class="required" for="stok">Stok Kuota Peminjaman</label>
                <input class="form-control {{ $errors->has('stok') ? 'is-invalid' : '' }}" type="number" name="stok" id="stok" value="{{ old('stok', '0') }}" step="1" required>
                <small class="text-muted">Stok akan terisi otomatis sesuai total aset yang kondisinya baik. Anda bisa menguranginya jika tidak semua unit boleh dipinjam mahasiswa.</small>
                @if($errors->has('stok'))
                    <div class="invalid-feedback">{{ $errors->first('stok') }}</div>
                @endif
            </div>

            <div class="form-group">
                <label for="deskripsi">Deskripsi / Kelengkapan Tambahan</label>
                <textarea class="form-control {{ $errors->has('deskripsi') ? 'is-invalid' : '' }}" name="deskripsi" id="deskripsi" rows="3">{{ old('deskripsi') }}</textarea>
                @if($errors->has('deskripsi'))
                    <div class="invalid-feedback">{{ $errors->first('deskripsi') }}</div>
                @endif
            </div>

            <div class="form-group">
                <label for="foto">Foto Barang (Opsional)</label>
                <input type="file" class="form-control-file {{ $errors->has('foto') ? 'is-invalid' : '' }}" name="foto" id="foto">
                @if($errors->has('foto'))
                    <div class="invalid-feedback">{{ $errors->first('foto') }}</div>
                @endif
            </div>

            <div class="form-group mt-4">
                <a href="{{ route('admin.barangs.master') }}" class="btn btn-secondary me-2">Batal</a>
                <button class="btn btn-success" type="submit">
                    <i class="fas fa-save me-1"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
@parent
<script>
$(document).ready(function() {
    $('.select2').select2({
        theme: 'bootstrap-5', // Sesuaikan jika kamu pakai BS4/BS5
        width: '100%'
    });

    // Fitur Auto-fill Stok
    $('#nama_barang').on('change', function() {
        let selectedOption = $(this).find('option:selected');
        let stokReal = selectedOption.data('stok');
        
        if (stokReal !== undefined) {
            // Auto-fill input stok
            $('#stok').val(stokReal);
            
            // Beri efek highlight sebentar biar user sadar angkanya berubah otomatis
            $('#stok').css('background-color', '#e8f5e9');
            setTimeout(() => $('#stok').css('background-color', ''), 1000);
        } else {
            $('#stok').val(0);
        }
    });
});
</script>
@endsection