{{-- resources/views/website/cart-items.blade.php --}}
@extends('website.layouts.main')

@section('title', 'Items in Cart | Guns & Wildlife Tactical Shop')

@push('meta')
<meta name="description" content="View and edit items in your cart. Update quantities or remove products before you complete your secure purchase.">
@endpush

@section('content')
    <div class="main-slider banner">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="slideContent wow fadeInLeft" data-wow-delay="0.4s">
                        <h3>Cart</h3>
                        <h2>Items</h2>
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

    <!-- Begin: Step 2 -->
    <div class="checkOutStyle">
        <div class="container">
            <div class="row">
                <div class="col-md-12 p-sm-0">
                    <div class="title">
                        <h2>Confirm Your Purchase</h2>
                    </div>
                </div>
            </div>

            @php
                $cartItems = [
                    ['image' => 'img4.png', 'title' => 'Product Title here', 'price' => '$200.00 USD', 'qty' => 1],
                    ['image' => 'img4.png', 'title' => 'Product Title here', 'price' => '$200.00 USD', 'qty' => 1],
                ];
            @endphp

            @foreach ($cartItems as $item)
                <div class="row cartItemCard">
                    <div class="col-md-1">
                        <img src="{{ asset('assets/images/' . $item['image']) }}" alt="{{ $item['title'] }}">
                    </div>
                    <div class="col-md-6 text-left">
                        <h4>{{ $item['title'] }}</h4>
                    </div>
                    <div class="col-md-2">
                        <strong class="price">{{ $item['price'] }}</strong>
                    </div>
                    <div class="col-md-2">
                        <div class="proCounter">
                            <span class="minus">-</span>
                            <input type="text" value="{{ $item['qty'] }}" />
                            <span class="plus">+</span>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <a href="#" class="delete"><i class="far fa-trash-alt"></i></a>
                    </div>
                </div>
            @endforeach

            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="text-center">
                        <a href="{{ route('payment') }}" class="themeBtn border-0 my-5">Proceed To Pay</a>
                    </div>
                    <ul class="shipping-billing-col">
                        <li>
                            <p><i class="fas fa-map-marker-alt"></i> 543 Flint Street, Atlanta, GA Georgia, 30303 <a href="" class="edit">edit</a></p>
                        </li>
                        <li>
                            <p><i class="fas fa-phone"></i> <a href="tel:1234567890">123 456 7890</a> <a href="#" class="edit">edit</a></p>
                        </li>
                        <li>
                            <p><i class="fas fa-envelope"></i><a href="mailto:info@demolink.com">info@demolink.com</a><a href="#" class="edit">edit</a></p>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Step 2 -->
@endsection