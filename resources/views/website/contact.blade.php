@extends('website.layouts.main')

@section('title', 'Contact Guns & Wildlife | Legal Firearm Sales & Support Pakistan')

@push('meta')
<meta name="description" content="Have questions about licensing, training, or firearm purchases? Contact Guns & Wildlife – Pakistan's reliable name in tactical arms.">
@endpush

@section('content')
    @php
        $selectedLabel = $formOptions[$selectedForm] ?? 'Contact Request';
        $optionDescriptions = [
            'dealer_account' => 'Dealer account request ke liye basic details submit karein.',
            'responder_account' => 'Military, law, ya responder account request bhejein.',
            'repair_return' => 'Repair ya return support request yahan submit karein.',
            'ffl_shipping' => 'Shipping document ya FFL related request submit karein.',
            'missing_item' => 'Missing item issue humein detail ke sath batayein.',
        ];
    @endphp
    <div class="main-slider banner">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="slideContent">
                        <h3>Contact</h3>
                        <h2>Contact Us</h2>
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

    <section class="cntctForms wow fadeInUp" data-wow-delay="0.4s">
        <div class="container">
            <h2>FORMS AND CONTACTS</h2>
            <div class="row">
                <div class="col-md-6">
                    <a href="{{ route('contact', ['form' => 'dealer_account']) }}#contact-request-form">Request an Anderson Manufacturing Dealer Account</a>
                </div>
                <div class="col-md-6">
                    <a href="{{ route('contact', ['form' => 'responder_account']) }}#contact-request-form">Request Military/Law/1st Responder account</a>
                </div>
                <div class="col-md-6">
                    <a href="{{ route('contact', ['form' => 'repair_return']) }}#contact-request-form">Submit Repair and Return Form (RMA)</a>
                </div>
                <div class="col-md-6">
                    <a href="{{ route('contact', ['form' => 'ffl_shipping']) }}#contact-request-form">If You Ordered an Item that Requires an FFL for Shipping</a>
                </div>
                <div class="col-md-6">
                    <a href="{{ route('contact', ['form' => 'missing_item']) }}#contact-request-form">Submit Missing Item Form</a>
                </div>
                <div class="col-md-6">
                    <a href="{{ url('/faq') }}">Frequently Asked Questions (FAQ)</a>
                </div>
            </div>
        </div>
    </section>

    <section class="cntctPage wow fadeInUp" data-wow-delay="0.4s" id="contact-request-form">
        <div class="container">
            <div class="proHead">
                <h2 class="sectionHeading">{{ $selectedLabel }}</h2>
                <p>{{ $optionDescriptions[$selectedForm] ?? 'Apni request ke liye required details fill karein.' }}</p>
            </div>

            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <form action="{{ route('contact.store') }}" method="POST" class="requstForm">
                @csrf
                <input type="hidden" name="form_type" value="{{ $selectedForm }}">

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
                        <label>Company / Organization</label>
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
                        <button type="submit" class="themeBtn">Submit Request</button>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <section class="becomeDealer cntctDealer">
        <img src="{{ asset('assets/images/gun5.png') }}" class="img-fluid gun5" alt="img">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <div class="dealrImg wow fadeInLeft" data-wow-delay="0.4s">
                        <figure><img src="{{ asset('assets/images/img18.jpg') }}" class="img-fluid" alt="img"></figure>
                        <a href="{{ url('/become-a-dealer') }}">Become A Dealer</a>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="dealrImg wow fadeInRight" data-wow-delay="0.4s">
                        <figure><img src="{{ asset('assets/images/img19.jpg') }}" class="img-fluid" alt="img"></figure>
                        <a href="{{ url('/find-a-dealer') }}">Find a Dealer</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Include Newsletter --}}
    @include('website.partials.newsletter')
@endsection
