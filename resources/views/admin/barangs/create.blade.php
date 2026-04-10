@extends('layouts.admin')
@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-plus-circle me-2 text-success"></i>Tambah Master Barang</h5>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.barangs.store") }}" enctype="multipart/form-data" id="formBarang">
            @csrf
            
            <div class="alert alert-info small mb-4">
                <i class="fas fa-info-circle me-1"></i> Data barang terintegrasi dengan <strong>Aset Fakultas</strong>. Jika barang berupa Bahan Habis Pakai (Non-Aset), silakan aktifkan mode Input Manual.
            </div>

            <div class="form-check form-switch mb-4 p-3 bg-light rounded border">
                <input class="form-check-input ms-0 me-3" type="checkbox" id="is_manual" style="transform: scale(1.3); cursor: pointer;">
                <label class="form-check-label fw-bold text-primary" for="is_manual" style="cursor: pointer;">
                    Input Manual (Barang Non-Aset / Bahan Habis Pakai)
                </label>
            </div>

            <div class="form-group">
                <label class="required" for="nama_barang">Nama Barang</label>

                <input type="hidden" name="nama_barang" id="nama_barang_real" value="{{ old('nama_barang') }}">

                <div id="wrap_select">
                    <select class="form-control select2" id="nama_barang_select">
                        <option value="">-- Cari dan Pilih Barang dari Aset --</option>
                        @foreach($asetTersedia as $namaLengkap => $jumlah)
                            <option value="{{ $namaLengkap }}" data-stok="{{ $jumlah }}" {{ old('nama_barang') == $namaLengkap ? 'selected' : '' }}>
                                {{ $namaLengkap }} (Tersedia: {{ $jumlah }} unit)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div id="wrap_manual" style="display: none;">
                    <input type="text" class="form-control" id="nama_barang_manual" placeholder="Ketik nama barang manual..." value="{{ old('nama_barang') }}">
                </div>

                @if($errors->has('nama_barang'))
                    <div class="invalid-feedback d-block">{{ $errors->first('nama_barang') }}</div>
                @endif
            </div>

            <div class="form-group">
                <label class="required" for="stok">Stok Kuota Peminjaman</label>
                <input class="form-control {{ $errors->has('stok') ? 'is-invalid' : '' }}" type="number" name="stok" id="stok" value="{{ old('stok', '0') }}" step="1" required>
                <small class="text-muted" id="stok_help">Stok terisi otomatis sesuai Aset Fakultas.</small>
                @if($errors->has('stok'))
                    <div class="invalid-feedback">{{ $errors->first('stok') }}</div>
                @endif
            </div>

            <div class="form-group">
                <label for="deskripsi">Deskripsi / Kelengkapan Tambahan</label>
                <textarea class="form-control {{ $errors->has('deskripsi') ? 'is-invalid' : '' }}" name="deskripsi" id="deskripsi" rows="3">{{ old('deskripsi') }}</textarea>
            </div>

            <div class="form-group">
                <label for="foto">Foto Barang (Opsional)</label>
                <input type="file" class="form-control-file {{ $errors->has('foto') ? 'is-invalid' : '' }}" name="foto" id="foto">
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
    $('.select2').select2({ width: '100%' });

    // Fungsi untuk ganti UI berdasarkan mode (Aset vs Manual)
    function toggleMode() {
        if ($('#is_manual').is(':checked')) {
            $('#wrap_select').hide();
            $('#wrap_manual').show();
            $('#stok_help').text('Mode manual. Silakan ketik jumlah stok secara manual.');
            
            // Reset stok ke 0 kalau pindah ke manual dan nama masih kosong
            if(!$('#nama_barang_manual').val()) {
                $('#stok').val(0);
            }
        } else {
            $('#wrap_manual').hide();
            $('#wrap_select').show();
            $('#stok_help').text('Stok terisi otomatis sesuai Aset Fakultas.');
            $('#nama_barang_select').trigger('change'); // Trigger hitung ulang stok
        }
    }

    // Jalankan saat di-klik & saat load pertama
    $('#is_manual').on('change', toggleMode);
    toggleMode();

    // Auto-fill Stok dari Dropdown (Mode Aset)
    $('#nama_barang_select').on('change', function() {
        if(!$('#is_manual').is(':checked')) {
            let stokReal = $(this).find('option:selected').data('stok');
            if (stokReal !== undefined) {
                $('#stok').val(stokReal).css('background-color', '#e8f5e9');
                setTimeout(() => $('#stok').css('background-color', ''), 1000);
            } else {
                $('#stok').val(0);
            }
        }
    });

    // Validasi & Sinkronisasi sebelum Submit ke Laravel
    $('#formBarang').on('submit', function(e) {
        if ($('#is_manual').is(':checked')) {
            $('#nama_barang_real').val($('#nama_barang_manual').val());
        } else {
            $('#nama_barang_real').val($('#nama_barang_select').val());
        }

        // Cek jika kosong
        if(!$('#nama_barang_real').val()) {
            e.preventDefault();
            Swal.fire({ icon: 'error', title: 'Oops...', text: 'Nama Barang harus diisi atau dipilih!' });
        }
    });
});
</script>
@endsection