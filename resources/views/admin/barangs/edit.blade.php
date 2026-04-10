@extends('layouts.admin')
@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-edit me-2 text-primary"></i>Edit Master Barang</h5>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.barangs.update", [$barang->id]) }}" enctype="multipart/form-data">
            @method('PUT')
            @csrf
            
            <div class="form-group">
                <label class="required" for="nama_barang">Pilih Barang dari Aset</label>
                <select class="form-control select2 {{ $errors->has('nama_barang') ? 'is-invalid' : '' }}" name="nama_barang" id="nama_barang" required>
                    <option value="">-- Cari dan Pilih Barang --</option>
                    @foreach($asetTersedia as $namaLengkap => $jumlah)
                        <option value="{{ $namaLengkap }}" data-stok="{{ $jumlah }}" {{ (old('nama_barang') ? old('nama_barang') : $barang->nama_barang) == $namaLengkap ? 'selected' : '' }}>
                            {{ $namaLengkap }} (Tersedia di Aset: {{ $jumlah }} unit)
                        </option>
                    @endforeach
                    
                    {{-- Jaga-jaga jika barang lama tiba-tiba dihapus/rusak di Aset, tapi masih ada di riwayat peminjaman --}}
                    @if(!isset($asetTersedia[$barang->nama_barang]))
                        <option value="{{ $barang->nama_barang }}" selected>
                            {{ $barang->nama_barang }} (Data aset tidak ditemukan / Rusak)
                        </option>
                    @endif
                </select>
                @if($errors->has('nama_barang'))
                    <div class="invalid-feedback">{{ $errors->first('nama_barang') }}</div>
                @endif
            </div>

            <div class="form-group">
                <label class="required" for="stok">Stok Kuota Peminjaman</label>
                <input class="form-control {{ $errors->has('stok') ? 'is-invalid' : '' }}" type="number" name="stok" id="stok" value="{{ old('stok', $barang->stok) }}" step="1" required>
                <small class="text-muted">Ganti barang di atas untuk meng-update otomatis angka stok sesuai Aset Fakultas.</small>
                @if($errors->has('stok'))
                    <div class="invalid-feedback">{{ $errors->first('stok') }}</div>
                @endif
            </div>

            <div class="form-group">
                <label for="deskripsi">Deskripsi</label>
                <textarea class="form-control {{ $errors->has('deskripsi') ? 'is-invalid' : '' }}" name="deskripsi" id="deskripsi" rows="3">{{ old('deskripsi', $barang->deskripsi) }}</textarea>
                @if($errors->has('deskripsi'))
                    <div class="invalid-feedback">{{ $errors->first('deskripsi') }}</div>
                @endif
            </div>

            <div class="form-group">
                <label for="foto">Foto Barang</label>
                @if($barang->foto)
                    <div class="mb-3 mt-2">
                        <img src="{{ asset('storage/' . $barang->foto) }}" alt="{{ $barang->nama_barang }}" class="img-thumbnail shadow-sm" width="200">
                    </div>
                    <small class="text-muted d-block mb-2">Abaikan jika tidak ingin mengganti foto.</small>
                @endif
                <input type="file" class="form-control-file {{ $errors->has('foto') ? 'is-invalid' : '' }}" name="foto" id="foto">
                @if($errors->has('foto'))
                    <div class="invalid-feedback">{{ $errors->first('foto') }}</div>
                @endif
            </div>

            <div class="form-group mt-4">
                <a href="{{ route('admin.barangs.master') }}" class="btn btn-secondary me-2">Batal</a>
                <button class="btn btn-primary" type="submit">
                    <i class="fas fa-save me-1"></i> Perbarui
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
    $('.select2').select2({ width: '100%' });

    $('#nama_barang').on('change', function() {
        let selectedOption = $(this).find('option:selected');
        let stokReal = selectedOption.data('stok');
        
        if (stokReal !== undefined) {
            $('#stok').val(stokReal);
            $('#stok').css('background-color', '#e8f5e9');
            setTimeout(() => $('#stok').css('background-color', ''), 1000);
        } else {
            $('#stok').val(0);
        }
    });
});
</script>
@endsection