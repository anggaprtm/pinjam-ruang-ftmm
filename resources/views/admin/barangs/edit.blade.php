@extends('layouts.admin')
@section('content')

@php
    // Deteksi cerdas: Apakah nama_barang ini ada di daftar Aset?
    // Jika tidak ada, berarti ini barang manual (Habis Pakai).
    $isManualItem = !isset($asetTersedia[$barang->nama_barang]) && $barang->nama_barang != '';
@endphp

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-edit me-2 text-primary"></i>Edit Master Barang</h5>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.barangs.update", [$barang->id]) }}" enctype="multipart/form-data" id="formBarang">
            @method('PUT')
            @csrf
            
            <div class="form-check form-switch mb-4 p-3 bg-light rounded border">
                <input class="form-check-input ms-0 me-3" type="checkbox" id="is_manual" {{ $isManualItem ? 'checked' : '' }} style="transform: scale(1.3); cursor: pointer;">
                <label class="form-check-label fw-bold text-primary" for="is_manual" style="cursor: pointer;">
                    Input Manual (Barang Non-Aset / Bahan Habis Pakai)
                </label>
            </div>

            <div class="form-group">
                <label class="required" for="nama_barang">Nama Barang</label>

                <input type="hidden" name="nama_barang" id="nama_barang_real" value="{{ $barang->nama_barang }}">

                <div id="wrap_select">
                    <select class="form-control select2" id="nama_barang_select">
                        <option value="">-- Cari dan Pilih Barang dari Aset --</option>
                        @foreach($asetTersedia as $namaLengkap => $jumlah)
                            <option value="{{ $namaLengkap }}" data-stok="{{ $jumlah }}" {{ (!$isManualItem && $barang->nama_barang == $namaLengkap) ? 'selected' : '' }}>
                                {{ $namaLengkap }} (Tersedia: {{ $jumlah }} unit)
                            </option>
                        @endforeach
                        
                        {{-- Opsi fallback jika aset hilang --}}
                        @if(!$isManualItem && !isset($asetTersedia[$barang->nama_barang]))
                            <option value="{{ $barang->nama_barang }}" selected>
                                {{ $barang->nama_barang }} (Data aset tidak ditemukan / Rusak)
                            </option>
                        @endif
                    </select>
                </div>

                <div id="wrap_manual" style="display: none;">
                    <input type="text" class="form-control" id="nama_barang_manual" placeholder="Ketik nama barang manual..." value="{{ $isManualItem ? $barang->nama_barang : '' }}">
                </div>

                @if($errors->has('nama_barang'))
                    <div class="invalid-feedback d-block">{{ $errors->first('nama_barang') }}</div>
                @endif
            </div>

            <div class="form-group">
                <label class="required" for="stok">Stok Kuota Peminjaman</label>
                <input class="form-control {{ $errors->has('stok') ? 'is-invalid' : '' }}" type="number" name="stok" id="stok" value="{{ old('stok', $barang->stok) }}" step="1" required>
                <small class="text-muted" id="stok_help">Ganti barang untuk meng-update stok otomatis sesuai Aset.</small>
            </div>

            <div class="form-group">
                <label for="deskripsi">Deskripsi</label>
                <textarea class="form-control {{ $errors->has('deskripsi') ? 'is-invalid' : '' }}" name="deskripsi" id="deskripsi" rows="3">{{ old('deskripsi', $barang->deskripsi) }}</textarea>
            </div>

            <div class="form-group">
                <label for="foto">Foto Barang</label>
                @if($barang->foto)
                    <div class="mb-3 mt-2">
                        <img src="{{ asset('storage/' . $barang->foto) }}" alt="{{ $barang->nama_barang }}" class="img-thumbnail shadow-sm" width="200">
                    </div>
                    <small class="text-muted d-block mb-2">Abaikan jika tidak ingin mengganti foto.</small>
                @endif
                <input type="file" class="form-control-file" name="foto" id="foto">
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

    let isFirstLoad = true;

    function toggleMode() {
        if ($('#is_manual').is(':checked')) {
            $('#wrap_select').hide();
            $('#wrap_manual').show();
            $('#stok_help').text('Mode manual. Stok tidak terikat dengan data Aset.');
        } else {
            $('#wrap_manual').hide();
            $('#wrap_select').show();
            $('#stok_help').text('Ganti barang untuk meng-update stok otomatis sesuai Aset.');
            
            // Jangan trigger stok aset saat load pertama kali di menu edit
            // (agar stok manual yang sudah diedit user tidak tertimpa otomatis)
            if (!isFirstLoad) {
                $('#nama_barang_select').trigger('change');
            }
        }
    }

    $('#is_manual').on('change', function() {
        isFirstLoad = false;
        toggleMode();
    });
    
    toggleMode(); // init

    $('#nama_barang_select').on('change', function() {
        if(!$('#is_manual').is(':checked') && !isFirstLoad) {
            let stokReal = $(this).find('option:selected').data('stok');
            if (stokReal !== undefined) {
                $('#stok').val(stokReal).css('background-color', '#e8f5e9');
                setTimeout(() => $('#stok').css('background-color', ''), 1000);
            } else {
                $('#stok').val(0);
            }
        }
    });
    
    isFirstLoad = false;

    $('#formBarang').on('submit', function(e) {
        if ($('#is_manual').is(':checked')) {
            $('#nama_barang_real').val($('#nama_barang_manual').val());
        } else {
            $('#nama_barang_real').val($('#nama_barang_select').val());
        }

        if(!$('#nama_barang_real').val()) {
            e.preventDefault();
            Swal.fire({ icon: 'error', title: 'Oops...', text: 'Nama Barang harus diisi atau dipilih!' });
        }
    });
});
</script>
@endsection