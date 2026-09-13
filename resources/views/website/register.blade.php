{{-- resources/views/website/register.blade.php --}}
@extends('website.layouts.main')

@section('title', 'Create an Account | Join Guns & Wildlife Pakistan')

@push('meta')
<meta name="description" content="Register to buy legal firearms, gear, and accessories. Access exclusive deals and book shooting range sessions online.">
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
                        <h3>Register</h3>
                        <h2>Register</h2>
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
        <h2 class="badgeHeading">Register</h2>
    </div>

    <!-- Begin: Registration Form -->
    <section class="accountAccesSec">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-5">
                    <div class="whitebg">
                        <h2><span>Create an Account</span></h2>
                        <form action="{{ route('register.post') }}" method="POST" class="formStyle form-row">
                            @csrf
                            <div class="input-group">
                                <label>First Name<em>*</em></label>
                                <input type="text" name="first_name" class="form-control" required>
                            </div>
                            <div class="input-group">
                                <label>Last Name<em>*</em></label>
                                <input type="text" name="last_name" class="form-control" required>
                            </div>
                            <div class="input-group">
                                <label>Email Address<em>*</em></label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="input-group">
                                <label>Password<em>*</em></label>
                                <input type="password" name="password" class="form-control" placeholder="At least 6 characters" required>
                            </div>
                            <div class="input-group">
                                <label>Re-enter password<em>*</em></label>
                                <input type="password" name="password_confirmation" class="form-control" placeholder="At least 6 characters" required>
                            </div>
                            <div class="input-group justify-content-md-end">
                                <button type="submit" class="themeBtn border-0">Sign Up</button>
                            </div>
                        </form>
                        <div class="or"><span>or</span></div>
                        <ul class="list-unstyled socialIo justify-content-center mb-4">
                            <li><a href="#" class="fb" target="_blank"><i class="fab fa-facebook-f"></i></a></li>
                            <li><a href="#" class="insta" target="_blank"><i class="fab fa-instagram"></i></a></li>
                            <li><a href="#" class="twitr" target="_blank"><i class="fab fa-twitter"></i></a></li>
                        </ul>
                        <p>Already have an account? <a href="{{ route('login') }}">Sign In</a></p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- END: Registration Form -->
@endsection
