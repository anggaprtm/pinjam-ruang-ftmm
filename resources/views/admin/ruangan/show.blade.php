@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.ruangan.title') }}
    </div>

    <div class="card-body">
        <div class="form-group">
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.ruangan.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr>
                        <th>
                            {{ trans('cruds.ruangan.fields.id') }}
                        </th>
                        <td>
                            {{ $ruangan->id }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.ruangan.fields.nama') }}
                        </th>
                        <td>
                            {{ $ruangan->nama }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.ruangan.fields.deskripsi') }}
                        </th>
                        <td>
                            {{ $ruangan->deskripsi }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.ruangan.fields.kapasitas') }}
                        </th>
                        <td>
                            {{ $ruangan->kapasitas }}
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.ruangan.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
        </div>
    </div>
</div>



@endsection