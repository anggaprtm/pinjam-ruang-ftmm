@extends('layouts.landing')

@section('content')

<div class="hero-section" style="min-height: 100vh; display:flex; align-items:center;">
    <div class="hero-overlay"></div>

    <div class="container text-center position-relative">

        {{-- ICON --}}
        <div class="mb-4">
            <i class="fas fa-lock text-warning" style="font-size:80px;"></i>
        </div>

        {{-- TITLE --}}
        <h1 class="fw-bold text-white" style="font-size:72px;">403</h1>
        <h4 class="text-light mb-3">Akses ditolak</h4>

        {{-- DESC --}}
        <p class="text-light mb-4" style="opacity:0.85;">
            Anda tidak memiliki izin untuk mengakses halaman ini.
        </p>

        {{-- ACTION --}}
        <a href="{{ url('/') }}" class="btn btn-outline-light px-4 py-2 rounded-pill">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>

    </div>
</div>

@endsection