@extends('website.layouts.main')

@section('title', 'Checkout – Secure Payment | Guns & Wildlife Pakistan')

@push('meta')
<meta name="description" content="Enter billing and payment details securely to finalize your order. Your transaction is protected with SSL encryption.">
@endpush

@section('content')
    <div class="main-slider banner">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="slideContent wow fadeInLeft" data-wow-delay="0.4s">
                        <h3>Cart</h3>
                        <h2>Payment</h2>
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
        <h2 class="badgeHeading">Cart</h2>
    </div>

    <!-- Begin: Payment Step -->
    <div class="checkOutStyle">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-12 col-md-12">
                    <div class="title text-center">
                        <h2>Payment Methods</h2>
                    </div>
                </div>
                <div class="col-lg-8 col-md-12">
                    <form action="{{ route('order.place') }}" method="POST" class="row formStyle">
                        @csrf
                        <div class="col-md-12 mb-4 text-center">
                            <img src="{{ asset('assets/images/card-img.png') }}" alt="Credit Cards">
                        </div>
                        <div class="col-md-6">
                            <label>Card Number</label>
                            <input type="text" name="card_number" class="form-control" placeholder="CARD NUMBER" required>
                        </div>
                        <div class="col-md-6">
                            <label>Name on Card</label>
                            <input type="text" name="card_name" class="form-control" placeholder="CARD TITLE" required>
                        </div>
                        <div class="col-md-4">
                            <label>Expiration Date</label>
                            <input type="text" name="expiry" class="form-control" placeholder="MM/YY" required>
                        </div>
                        <div class="col-md-2">
                            <label>CVV</label>
                            <input type="text" name="cvv" class="form-control" placeholder="***" required>
                        </div>
                        <div class="col-md-6">
                            <div class="checkbox mt-4">
                                <input type="checkbox" id="box-2" name="save_card">
                                <label for="box-2">
                                    <h5>Save Card</h5>Information is encrypted and securely stored.
                                </label>
                            </div>
                        </div>
                        <div class="col-md-12 mt-4 text-center">
                            <button type="submit" class="themeBtn border-0">Place Order</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Payment Step -->
@endsection