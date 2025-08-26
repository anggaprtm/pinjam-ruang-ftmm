@extends('layouts.admin')
@section('content')

<h3 class="font-weight-bold mb-4">Pengaturan Akun</h3>

<div class="row">
    <div class="col-lg-12">
        {{-- Navigasi Tab --}}
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab" aria-controls="profile" aria-selected="true">Profil Saya</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password" type="button" role="tab" aria-controls="password" aria-selected="false">Ganti Password</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="danger-tab" data-bs-toggle="tab" data-bs-target="#danger" type="button" role="tab" aria-controls="danger" aria-selected="false">Hapus Akun</button>
            </li>
        </ul>

        {{-- Konten Tab --}}
        <div class="tab-content" id="myTabContent">
            {{-- Tab 1: Profil Saya --}}
            <div class="tab-pane fade show active" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                <div class="card form-card border-top-0 rounded-0 rounded-bottom">
                    <div class="card-body">
                        <form method="POST" action="{{ route("profile.password.updateProfile") }}">
                            @csrf
                            <div class="form-group mb-3">
                                <label class="form-label required" for="name">Nama</label>
                                <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text" name="name" id="name" value="{{ old('name', auth()->user()->name) }}" required>
                                @if($errors->has('name'))
                                    <div class="invalid-feedback">{{ $errors->first('name') }}</div>
                                @endif
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label required" for="email">Email (Username)</label>
                                <input class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" type="email" name="email" id="email" value="{{ old('email', auth()->user()->email) }}" required>
                                @if($errors->has('email'))
                                    <div class="invalid-feedback">{{ $errors->first('email') }}</div>
                                @endif
                            </div>
                            <div class="text-end">
                                <button class="btn btn-primary" type="submit">
                                    {{ trans('global.save') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Tab 2: Ganti Password --}}
            <div class="tab-pane fade" id="password" role="tabpanel" aria-labelledby="password-tab">
                <div class="card form-card border-top-0 rounded-0 rounded-bottom">
                    <div class="card-body">
                        <form method="POST" action="{{ route("profile.password.update") }}">
                            @csrf
                            <div class="form-group mb-3">
                                <label class="form-label required" for="password_new">Password Baru</label>
                                <input class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}" type="password" name="password" id="password_new" required>
                                @if($errors->has('password'))
                                    <div class="invalid-feedback">{{ $errors->first('password') }}</div>
                                @endif
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label required" for="password_confirmation">Ulangi Password Baru</label>
                                <input class="form-control" type="password" name="password_confirmation" id="password_confirmation" required>
                            </div>
                            <div class="text-end">
                                <button class="btn btn-primary" type="submit">
                                    {{ trans('global.save') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Tab 3: Zona Berbahaya --}}
            <div class="tab-pane fade" id="danger" role="tabpanel" aria-labelledby="danger-tab">
                <div class="card form-card border-top-0 rounded-0 rounded-bottom danger-zone">
                    <div class="card-header">
                        <h5 class="mb-0">Hapus Akun</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Setelah akun Anda dihapus, semua sumber daya dan data akan dihapus secara permanen. Sebelum menghapus akun Anda, harap unduh data atau informasi apa pun yang ingin Anda simpan.</p>
                        <form id="deleteAccountForm" method="POST" action="{{ route('profile.password.destroyProfile') }}">
                            @csrf
                            <button type="button" class="btn btn-danger" id="deleteAccountButton">
                                {{ trans('global.delete_account') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@parent
<script>
    document.getElementById('deleteAccountButton').addEventListener('click', function() {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Tindakan ini tidak dapat dibatalkan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus akun saya!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('deleteAccountForm').submit();
            }
        })
    });
</script>
@endsection
