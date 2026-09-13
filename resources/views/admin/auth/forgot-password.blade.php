@extends('admin.layouts.auth')

@section('title', 'Forgot Password | Guns and Wildlife')

@section('content')
<div class="auth-wrapper">
    <div class="row g-0 auth-row">
        <!-- Left Side: Image Section -->
        <div class="col-lg-6 d-none d-lg-block">
            <div class="auth-image-side" style="background-image: url('{{ asset('admin-assets/images/login.jpg') }}');">
                <div class="auth-overlay">
                    <div class="auth-logo-box text-center">
                        <h1 class="text-white loginHeading">Guns & Wildlife</h1>
                        <p class="text-white-50">Password Recovery</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Form Section -->
        <div class="col-lg-6">
            <div class="auth-form-side">
                <div class="auth-form-content">
                    <div class="mb-5">
                        <h2 class="boldHeading">Forgot Password?</h2>
                        <p class="text-muted">Enter your email and we'll send you a reset link.</p>
                    </div>

                    <form action="{{ route('admin.password.email') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                                <input type="email" name="email" class="form-control" placeholder="example@mail.com" required autofocus>
                            </div>
                        </div>

                        <button type="submit" class="themeBtn themeBtn--primary w-100">
                            Send Reset Link <i class="fa-solid fa-paper-plane"></i>
                        </button>

                        <p class="text-center mt-4 text-muted">
                            Remember your password? <a href="{{ route('admin.login') }}" class="text-theme text-decoration-none">Back to Login</a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
