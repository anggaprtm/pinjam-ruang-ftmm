@extends('layouts.landing')

@section('content')

<div class="hero-section" style="min-height: 100vh; display:flex; align-items:center;">
    <div class="hero-overlay"></div>

    <div class="container text-center position-relative">
        
        {{-- ICON --}}
        <div class="mb-4">
            <i class="fas fa-search-minus text-light" style="font-size:80px; opacity:0.8;"></i>
        </div>

        {{-- TITLE --}}
        <h1 class="fw-bold text-white" style="font-size:72px;">404</h1>
        <h4 class="text-light mb-3">Halaman tidak ditemukan</h4>

        {{-- DESC --}}
        <p class="text-light mb-4" style="opacity:0.85;">
            Sepertinya halaman yang Anda cari tidak tersedia atau telah dipindahkan.
        </p>

        {{-- ACTION --}}
        <a href="{{ url('/') }}" class="btn btn-primary-custom px-4 py-2 rounded-pill">
            <i class="fas fa-home me-1"></i> Kembali ke Beranda
        </a>

    </div>
</div>

@endsection