@extends('admin.layouts.auth')

@section('title', 'Sign Up | Guns and Wildlife')

@section('content')
<div class="auth-wrapper">
    <div class="row g-0 auth-row">
        <!-- Left Side: Image Section -->
        <div class="col-lg-6 d-none d-lg-block">
            <div class="auth-image-side" style="background-image: url('{{ asset('admin-assets/images/login.jpg') }}');">
                <div class="auth-overlay">
                    <div class="auth-logo-box text-center">
                        <h1 class="text-white loginHeading">Guns & Wildlife</h1>
                        <p class="text-white-50">Create Your Account</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Form Section -->
        <div class="col-lg-6">
            <div class="auth-form-side">
                <div class="auth-form-content">
                    <div class="mb-5">
                        <h2 class="boldHeading">Get Started</h2>
                        <p class="text-muted">Fill in the details to create your account.</p>
                    </div>

                    <form action="{{ route('admin.register.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                                <input type="text" name="name" class="form-control" placeholder="John Doe" required autofocus>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                                <input type="email" name="email" class="form-control" placeholder="example@mail.com" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                                <input type="password" name="password_confirmation" class="form-control" placeholder="••••••••" required>
                            </div>
                        </div>

                        <button type="submit" class="themeBtn themeBtn--primary w-100">
                            Create Account <i class="fa-solid fa-user-plus"></i>
                        </button>

                        <p class="text-center mt-4 text-muted">
                            Already have an account? <a href="{{ route('admin.login') }}" class="text-theme text-decoration-none">Sign In</a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
