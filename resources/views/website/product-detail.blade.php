@extends('website.layouts.main')

@section('title', 'Badger Tactical Shirt & Cap Combo | Guns & Wildlife Product')

@push('meta')
<meta name="description" content="Explore detailed features, specs, and reviews of our Badger Tactical Combo. Shop licensed tactical gear online in Pakistan.">
@endpush

@section('content')
    <main class="shopBg">
        <div class="main-slider banner">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="slideContent">
                            <h3>Product</h3>
                            <h2>Product Detail</h2>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="gunRghtimg">
                        <figure><img src="{{ asset('assets/images/dealer.webp') }}" class="img-fluid" alt="img"></figure>
                        </div>
                    </div>
                </div>
            </div>
            <h2 class="badgeHeading">Product</h2>
        </div>
    </main>

    <section class="productDetail">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="product_slider wow fadeInRight">
                        @php
                            $productImages = ['img5.png', 'img10.png', 'img99.png', 'img88.png', 'img66.png'];
                        @endphp
                        @foreach ($productImages as $image)
                            <div class="clientss_right">
                                <img src="{{ asset('assets/images/' . $image) }}" class="w-100" alt="Product Image">
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="detail">
                        <h2>BADGER TACTICAL COMBO - SHIRT & CAP</h2>
                        <div class="rateNprice">
                            <ul class="star">
                                @for ($i = 0; $i < 5; $i++)
                                    <li><i class="far fa-star"></i></li>
                                @endfor
                                <li><span>3 Review(s)</span></li>
                            </ul>
                            <a href="#" class="clRed">Add a Review</a>
                        </div>
                        <h3 class="clRed"><del>₨89.99</del> ₨50.99</h3>
                        <span class="availability">Availability: <span class="clRed">In stock</span></span>
                        <h4>Guns & WildLife</h4>
                        <p>
                            Stay sharp and look the part with our premium Badger Tactical T-Shirt and Cap Combo –
                            designed for shooters, outdoor enthusiasts, and tactical professionals across Pakistan.
                        </p>
                        <p>
                            Crafted with breathable cotton and detailed stitching, this combo delivers both style and
                            utility for range days, hunting trips, or casual wear.
                        </p>
                        <p>
                            The t-shirt features a bold Badger Tactical logo while the adjustable cap ensures a snug,
                            all-day fit.
                        </p>

                        <ul>
                            <li>Premium cotton material</li>
                            <li>Unisex fit – all sizes available</li>
                            <li>Durable embroidery logo</li>
                            <li>Perfect for gun range & outdoor wear</li>
                        </ul>

                        <div class="proCounter mr-4">
                            <span class="minus"><i class="fa fa-angle-down"></i></span>
                            <input type="text" value="1" />
                            <span class="plus"><i class="fa fa-angle-up"></i></span>
                        </div>
                        <div class="cartBtn">
                            <a href="{{ url('/cart') }}" class="themeBtn">Add to Cart <i
                                    class="fa fa-shopping-cart mr-2"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Include Newsletter --}}
    @include('website.partials.newsletter')
@endsection