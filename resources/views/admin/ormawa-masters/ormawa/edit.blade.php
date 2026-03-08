@extends('layouts.admin')
@section('content')
<h3>Edit Ormawa</h3>
<form method="POST" action="{{ route('admin.ormawas-master.update', $item->id) }}">@method('PUT') @include('admin.ormawa-masters.ormawa.form')</form>
@endsection
