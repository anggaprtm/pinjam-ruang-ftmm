@extends('layouts.admin')
@section('content')
<div class="d-flex align-items-center mb-3">
    <h3 class="mb-0">Tambah Flow Verifikasi</h3>
    <div class="ms-auto">
        <a href="{{ route('admin.sik-flows.index') }}" class="btn btn-secondary">Kembali</a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form action="{{ route('admin.sik-flows.store') }}" method="POST">
            @include('admin.sik-flows._form')
        </form>
    </div>
</div>
@endsection
