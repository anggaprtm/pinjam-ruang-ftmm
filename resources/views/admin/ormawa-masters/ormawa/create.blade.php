@extends('layouts.admin')
@section('content')
<h3>Tambah Ormawa</h3>
<form method="POST" action="{{ route('admin.ormawas-master.store') }}">@include('admin.ormawa-masters.ormawa.form')</form>
@endsection
