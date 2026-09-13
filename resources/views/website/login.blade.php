{{-- resources/views/website/login.blade.php --}}
@extends('website.layouts.main')

@section('title', 'Login to Your Account | Guns & Wildlife Pakistan')

@push('meta')
<meta name="description" content="Access your account to view orders, manage cart, and track firearm-related purchases securely on Guns & Wildlife.">
@endpush

@push('styles')
<style>
    header {
        z-index: 10;
    }

    .auth-banner {
        overflow: hidden;
        position: relative;
        z-index: 1;
    }

    .auth-banner .gunRghtimg,
    .auth-banner .gun-3d {
        pointer-events: none;
    }

    .auth-banner .gun-3d {
        height: 560px;
        margin-top: -80px;
        margin-bottom: -80px;
    }

    .accountAccesSec,
    footer {
        position: relative;
        z-index: 1;
    }
</style>
@endpush

@section('content')
    <div class="main-slider banner auth-banner">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="slideContent wow fadeInLeft" data-wow-delay="0.4s">
                        <h3>Login</h3>
                        <h2>Login Now</h2>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="gunRghtimg wow fadeInRight" data-wow-delay="0.4s">
                        {{-- <figure><img src="{{ asset('assets/images/gun1.png') }}" class="img-fluid" alt="img"></figure> --}}
                        <figure><img src="{{ asset('assets/images/dealer.webp') }}" class="img-fluid" alt="img"></figure>
                    </div>
                </div>
            </div>
        </div>
        <h2 class="badgeHeading">Login</h2>
    </div>

    <!-- Begin: Login Form -->
    <section class="accountAccesSec">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-5">
                    <div class="whitebg">
                        <h2><span>Welcome back!</span>Sign in to your account</h2>
                        <form action="{{ route('login.post') }}" method="POST" class="formStyle form-row">
                            @csrf
                            <div class="input-group">
                                <label>Email or Mobile<em>*</em></label>
                                <input type="text" name="login" class="form-control" placeholder="Enter your Email or Mobile" required>
                            </div>
                            <div class="input-group">
                                <label>Password<em>*</em></label>
                                <input type="password" name="password" class="form-control" placeholder="********" required>
                            </div>
                            <div class="input-group justify-content-sm-between align-items-sm-center">
                                <button type="submit" class="btnStyle rounded">Sign In</button>
                                <a href="{{ route('password.reset') }}" class="forgetPass">Forgot my password</a>
                            </div>
                        </form>
                        <div class="or"><span>or</span></div>
                        <ul class="list-unstyled socialIo justify-content-center mb-4">
                            <li><a href="#" class="fb" target="_blank"><i class="fab fa-facebook-f"></i></a></li>
                            <li><a href="#" class="insta" target="_blank"><i class="fab fa-instagram"></i></a></li>
                            <li><a href="#" class="twitr" target="_blank"><i class="fab fa-twitter"></i></a></li>
                        </ul>
                        <p>Don't have an account? <a href="{{ route('register') }}">Sign Up</a></p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- END: Login Form -->
@endsection
