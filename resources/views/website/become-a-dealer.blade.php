{{-- resources/views/website/become-a-dealer.blade.php --}}
@extends('website.layouts.main')

@section('title', 'Authorized Firearms Dealers in Pakistan | Guns & Wildlife Network')

@push('meta')
<meta name="description" content="Find official Guns & Wildlife dealers near you. Connect with licensed firearm suppliers across Pakistan for secure purchases and support.">
@endpush

@section('content')
    <div class="main-slider banner">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="slideContent">
                        <h3>Become a</h3>
                        <h2>Dealer</h2>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="gunRghtimg">
                        <img src="{{ asset('assets/images/dealer.webp') }}" class="img-fluid" alt="">    
                    </div>
                </div>
            </div>
        </div>
        <h2 class="badgeHeading">Contact</h2>
    </div>

    <section class="cntctPage wow fadeInUp" data-wow-delay="0.4s">
        <div class="container">
            <div class="proHead">
                <h2 class="sectionHeading">REQUEST DEALER ACCOUNT</h2>
                <p>Basic details submit karein. Admin aap se zarurat par contact kar lega.</p>
            </div>

            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <form action="{{ route('dealer.request') }}" method="POST" enctype="multipart/form-data" class="requstForm">
                @csrf
                <input type="hidden" name="form_type" value="dealer_account">
                <div class="row">
                    <div class="col-md-6">
                        <label>Full Name *</label>
                        <input type="text" name="full_name" value="{{ old('full_name') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label>Email *</label>
                        <input type="email" name="email" value="{{ old('email') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label>Phone *</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label>Company Name</label>
                        <input type="text" name="company_name" value="{{ old('company_name') }}">
                    </div>
                    <div class="col-md-6">
                        <label>City</label>
                        <input type="text" name="city" value="{{ old('city') }}">
                    </div>
                    <div class="col-md-12">
                        <label>Short Details</label>
                        <textarea name="message">{{ old('message') }}</textarea>
                    </div>
                    <div class="col-md-12">
                        <button type="submit" class="themeBtn">Send It</button>
                    </div>
                </div>
            </form>
        </div>
    </section>
@endsection
