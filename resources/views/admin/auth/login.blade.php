@extends('admin.layouts.auth')

@section('title', 'Login | Guns and Wildlife')

@section('content')
<div class="auth-wrapper">
    <div class="row g-0 auth-row">
        <!-- Left Side: Image Section -->
        <div class="col-lg-6 d-none d-lg-block">
            <div class="auth-image-side" style="background-image: url('{{ asset('admin-assets/images/login.jpg') }}');">
                <div class="auth-overlay">
                    <div class="auth-logo-box text-center">
                        <h1 class="text-white loginHeading">Guns & Wildlife</h1>
                        <p class="text-white-50">Management System</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Form Section -->
        <div class="col-lg-6">
            <div class="auth-form-side">
                <div class="auth-form-content">
                    <div class="mb-5">
                        <h2 class="boldHeading">Welcome Back</h2>
                        <p class="text-muted">Please enter your details to sign in.</p>
                    </div>

                    @if (session('status'))
                        <div class="alert alert-success">{{ session('status') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">{{ $errors->first() }}</div>
                    @endif

                    <form action="{{ route('admin.login.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                                <input type="email" name="email" class="form-control" placeholder="example@mail.com" value="{{ old('email') }}" required autofocus>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="rememberMe">
                                <label class="form-check-label" for="rememberMe">Remember Me</label>
                            </div>
                            <a href="{{ route('admin.password.request') }}" class="text-theme text-decoration-none">Forgot Password?</a>
                        </div>

                        <button type="submit" class="themeBtn themeBtn--primary w-100">
                            Sign In <i class="fa-solid fa-arrow-right"></i>
                        </button>

                        <p class="text-center mt-4 text-muted">
                            Authorized roles: Admin, Salesman, Partner
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
