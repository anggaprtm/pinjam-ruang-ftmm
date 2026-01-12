@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        Edit Barang
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.barangs.update", [$barang->id]) }}" enctype="multipart/form-data">
            @method('PUT')
            @csrf
            <div class="form-group">
                <label class="required" for="nama_barang">Nama Barang</label>
                <input class="form-control {{ $errors->has('nama_barang') ? 'is-invalid' : '' }}" type="text" name="nama_barang" id="nama_barang" value="{{ old('nama_barang', $barang->nama_barang) }}" required>
                @if($errors->has('nama_barang'))
                    <div class="invalid-feedback">
                        {{ $errors->first('nama_barang') }}
                    </div>
                @endif
            </div>
            <div class="form-group">
                <label for="deskripsi">Deskripsi</label>
                <textarea class="form-control {{ $errors->has('deskripsi') ? 'is-invalid' : '' }}" name="deskripsi" id="deskripsi">{{ old('deskripsi', $barang->deskripsi) }}</textarea>
                @if($errors->has('deskripsi'))
                    <div class="invalid-feedback">
                        {{ $errors->first('deskripsi') }}
                    </div>
                @endif
            </div>
            <div class="form-group">
                <label class="required" for="stok">Stok</label>
                <input class="form-control {{ $errors->has('stok') ? 'is-invalid' : '' }}" type="number" name="stok" id="stok" value="{{ old('stok', $barang->stok) }}" step="1" required>
                @if($errors->has('stok'))
                    <div class="invalid-feedback">
                        {{ $errors->first('stok') }}
                    </div>
                @endif
            </div>
            <div class="form-group">
                <label for="foto">Foto Barang</label>
                @if($barang->foto)
                    <div class="mb-2">
                        <img src="{{ asset('storage/' . $barang->foto) }}" alt="{{ $barang->nama_barang }}" class="img-thumbnail" width="200">
                    </div>
                    <small class="form-text text-muted">Abaikan jika tidak ingin mengganti foto.</small>
                @endif
                <input type="file" class="form-control-file {{ $errors->has('foto') ? 'is-invalid' : '' }}" name="foto" id="foto">
                @if($errors->has('foto'))
                    <div class="invalid-feedback">{{ $errors->first('foto') }}</div>
                @endif
            </div>
            <div class="form-group">
                <button class="btn btn-danger" type="submit">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
