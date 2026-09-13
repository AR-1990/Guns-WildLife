{{-- resources/views/website/cart.blade.php --}}
@extends('website.layouts.main')

@section('title', 'Your Shopping Cart | Guns & Wildlife Pakistan')

@push('meta')
<meta name="description" content="Review your selected firearms, accessories, and gear before checkout. Secure your cart and proceed to payment easily.">
@endpush

@section('content')
    <div class="main-slider banner">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="slideContent wow fadeInLeft" data-wow-delay="0.4s">
                        <h3>Cart</h3>
                        <h2>Details</h2>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="gunRghtimg wow fadeInRight" data-wow-delay="0.4s">
                        <figure><img src="{{ asset('assets/images/dealer.webp') }}" class="img-fluid" alt="img"></figure>
                        
                    </div>
                </div>
            </div>
        </div>
        <h2 class="badgeHeading">Cart</h2>
    </div>

    <!-- Begin: Step 1 -->
    <div class="checkOutStyle">
        <div class="container">
            <form action="{{ route('checkout.submit') }}" method="POST" class="row justify-content-center formStyle">
                @csrf
                <div class="col-md-12">
                    <div class="title inner">
                        <h2>Billing Address</h2>
                        <h4>Fill the form below to complete your purchase</h4>
                        <p class="checkout-subheading"><span>Already Registered?</span> Click here to <a href="{{ route('login') }}">Login now</a></p>
                    </div>
                </div>
                <div class="col-md-6">
                    <label>First Name</label>
                    <input type="text" name="first_name" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label>Last Name</label>
                    <input type="text" name="last_name" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label>Email Address</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label>Phone</label>
                    <input type="text" name="phone" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control">
                </div>
                <div class="col-md-6">
                    <label>Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control">
                </div>
                <div class="col-md-12">
                    <label>Address</label>
                    <input type="text" name="address" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label>Country</label>
                    <input type="text" name="country" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label>City</label>
                    <input type="text" name="city" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label>Zip/Postal Code</label>
                    <input type="text" name="zip" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label>State/Province</label>
                    <input type="text" name="state" class="form-control">
                </div>
                <div class="col-md-12">
                    <div class="checkbox">
                        <input type="checkbox" id="box-1" name="create_account">
                        <label for="box-1">Create an account for later use</label>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="checkbox">
                        <input type="checkbox" id="box-2" name="same_address" checked>
                        <label for="box-2">Ship to the same address mentioned above</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="row order-summery no-gutters">
                        <div class="col-md-12 title my-5 text-center">
                            <h2>Order Summary</h2>
                        </div>
                        <div class="col-md-12 d-flex align-items-center justify-content-between">
                            <span>Subtotal (3 items)</span>
                            <strong>USD 75.00</strong>
                        </div>
                        <hr class="w-100">
                        <div class="col-md-12 d-flex align-items-center justify-content-between">
                            <span>Shipping fee</span>
                            <strong>USD 5.00</strong>
                        </div>
                        <hr class="w-100">
                        <div class="col-md-12">
                            <div class="applyCoupon">
                                <input type="text" name="voucher" class="form-control" placeholder="Enter Voucher Code">
                                <button type="button" class="themeBtn border-0">Apply</button>
                            </div>
                        </div>
                        <hr class="w-100">
                        <div class="col-md-12 d-flex align-items-center justify-content-between">
                            <span>Total</span>
                            <strong>USD 80.00</strong>
                        </div>
                        <hr class="w-100">
                        <div class="col-md-12 text-center mt-4">
                            <button type="submit" class="themeBtn border-0">Proceed to Checkout</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Step 1 -->
@endsection
