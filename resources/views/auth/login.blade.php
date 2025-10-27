<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>Login — {{ config('app.name') }}</title>

    <!-- Asset statis Bootstrap/FontAwesome/CSS kamu -->
    <link rel="stylesheet" href="{{ asset('assets/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fontawesome/css/all.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
</head>

<body>
<div id="app">
    <section class="section">
        <div class="d-flex flex-wrap align-items-stretch">

            <!-- Form -->
            <div class="col-lg-4 col-md-6 col-12 order-lg-1 min-vh-100 order-2 bg-white">
                <div class="p-4 m-3">
                    <h5 class="text-dark font-weight-normal pt-5 mt-5">
                        Aplikasi <span class="font-weight-bold">PinjamRuang FTMM</span>
                    </h5>

                    {{-- Session status (mis. setelah reset password) --}}
                    @if (session('status'))
                        <div class="alert alert-success">{{ session('status') }}</div>
                    @endif

                    {{-- Error global (optional) --}}
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            {{ __('Terjadi kesalahan. Periksa kembali input Anda.') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="needs-validation" novalidate>
                        @csrf

                        {{-- Email atau NIP --}}
                        <div class="form-group">
                            <label for="login">Email atau NIP</label>
                            <input id="login" type="text"
                                   class="form-control @error('login') is-invalid @enderror"
                                   name="login" value="{{ old('login') }}" required autofocus
                                   autocomplete="username">
                            @error('login')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        {{-- Password --}}
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input id="password" type="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   name="password" required autocomplete="current-password">
                            @error('password')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        {{-- Remember me --}}
                        <div class="form-group form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember"
                                   {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember">Ingat Saya</label>
                        </div>

                        <div class="form-group text-right">
                            <button type="submit" class="btn btn-primary btn-lg btn-icon icon-right">
                                Login
                            </button>
                        </div>

                        <div class="mt-3 text-center">
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}">Lupa password?</a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <!-- Background kanan -->
            <div class="col-lg-8 col-12 order-lg-2 order-1 min-vh-100 background-walk-y position-relative overlay-gradient-bottom"
                 style="background-image: url('{{ asset('assets/img/unsplash/login-bgs.jpg') }}');">
                <div class="absolute-bottom-left index-2">
                    <div class="text-light p-5 pb-2">
                        <div class="mb-5 pb-3">
                            <h1 class="mb-2 display-4 font-weight-bold" id="greetings"></h1>
                            <h5 class="font-weight-normal text-muted-transparent">FTMM, Gedung Nano</h5>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>

<!-- Minimal JS -->
<script src="{{ asset('assets/js/jquery-3.5.1.min.js') }}"></script>
<script src="{{ asset('assets/bootstrap/js/bootstrap.min.js') }}"></script>

@include('layouts.partials.greetings')
<script>
    $(function(){ $("#greetings").html(greetings()); });
</script>
</body>
</html>
