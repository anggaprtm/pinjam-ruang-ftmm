@extends('layouts.admin')

@section('content')
<div class="content">

    <h3 class="mb-4 fw-bold">Tambah Signage Config</h3>

    <div class="card shadow-sm border-0">
        <div class="card-body">

            <form method="POST" action="{{ route('admin.display-config.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-bold">Lokasi</label>
                    <input type="text" name="location" class="form-control" placeholder="lantai6" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Mode</label>
                    <select name="mode" class="form-control">
                        <option value="dashboard">Dashboard</option>
                        <option value="announcement">Announcement</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Content Type</label>
                    <select name="content_type" class="form-control">
                        <option value="image">Image</option>
                        <option value="text">Text</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Content Value (optional)</label>
                    <textarea name="content_value" class="form-control"></textarea>
                </div>

                {{-- 🔥 UPLOAD --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">Upload Image</label>
                    <input type="file" name="image" class="form-control">
                </div>

                <button class="btn btn-primary shadow-sm">
                    <i class="fas fa-save me-1"></i> Simpan
                </button>

            </form>

        </div>
    </div>

</div>
@endsection