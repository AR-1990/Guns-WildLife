{{-- resources/views/website/reset-password.blade.php --}}
@extends('website.layouts.main')

@section('title', 'Reset Your Password | Guns & Wildlife Secure Login')

@push('meta')
<meta name="description" content="Forgot your password? Easily reset and regain access to your Guns & Wildlife account using your email or mobile.">
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
                        <h3>Reset</h3>
                        <h2>Password</h2>
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
        <h2 class="badgeHeading">Password</h2>
    </div>

    <!-- Begin: Reset Password Form -->
    <section class="accountAccesSec">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-5">
                    <div class="whitebg">
                        <h2><span>Reset Password</span></h2>
                        <form action="{{ route('password.update') }}" method="POST" class="formStyle form-row">
                            @csrf
                            <input type="hidden" name="token" value="{{ request('token') }}">
                            <div class="input-group">
                                <label>New Password<em>*</em></label>
                                <input type="password" name="password" class="form-control" placeholder="At least 6 characters" required>
                            </div>
                            <div class="input-group">
                                <label>Confirm Password<em>*</em></label>
                                <input type="password" name="password_confirmation" class="form-control" placeholder="At least 6 characters" required>
                            </div>
                            <div class="input-group justify-content-md-end">
                                <button type="submit" class="themeBtn border-0">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- END: Reset Password Form -->

    {{-- Include Newsletter --}}
    @include('website.partials.newsletter')
@endsection
