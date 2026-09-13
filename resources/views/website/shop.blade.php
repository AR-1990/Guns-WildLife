{{-- resources/views/website/shop.blade.php --}}
@extends('website.layouts.main')

@section('title', 'Shop Rifles, Tactical Gear & Accessories Online | Guns & Wildlife Pakistan')

@push('meta')
<meta name="description" content="Browse our online store for complete rifles, parts, clothing, and tactical gear. Licensed and ready-to-ship products across Pakistan.">
@endpush

@section('content')
    <main class="shopBg">
        <div class="main-slider banner">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="slideContent">
                            <h3>Shop</h3>
                            <h2>Shop</h2>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="gunRghtimg">
                            <!-- <model-viewer src="{{ asset('assets/images/Machine Gun.glb') }}" class="gun-3d" alt="A 3D model of a firearm"
                                ar ar-modes="webxr scene-viewer quick-look" environment-image="neutral" auto-rotate
                                disable-zoom camera-controls></model-viewer> -->

                            <img src="{{ asset('assets/images/shop.png') }}" class="img-fluid" alt="">    
                        </div>
                    </div>
                </div>
            </div>
            <h2 class="badgeHeading">Shop</h2>
        </div>
    </main>

    <section class="shopPage">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    <div class="shpList">
                        <h3>STORE CATEGORIES</h3>
                        <ul class="nav nav-tabs">
                            @php
                                $categories = [
                                    'ALL',
                                    'SHOP IN STOCK ITEMS',
                                    'COMPLETE RIFLES',
                                    'COMPLETE LOWERS',
                                    'COMPLETE UPPERS',
                                    'COMPLETE BARRELS',
                                    'COMPLETE BOLT CARRIERS',
                                    'COMPLETE KITS',
                                    'MAGAZINES',
                                    'RETAIL PACKAGED',
                                    'RF 85',
                                    'SHIRTS AND HATS',
                                    'GLOCK',
                                ];
                            @endphp
                            @foreach ($categories as $key => $category)
                                <li class="nav-item">
                                    <a class="nav-link {{ $key == 0 ? 'active' : '' }}" href="#">{{ $category }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="row">
                        @php
                            $products = [
                                ['image' => 'img4.png', 'title' => 'Complete Rifles', 'desc' => 'Licensed weapons for civilian or defense use', 'price' => '₨9,500 – ₨10,500'],
                                ['image' => 'img5.png', 'title' => 'Caps & Tees', 'desc' => 'Guns & Wildlife branded apparel', 'price' => '₨1,500 – ₨3,500'],
                                ['image' => 'img6.png', 'title' => 'Parts & Accessories', 'desc' => 'Barrels, mags, triggers & more', 'price' => '₨9,500 – ₨10,500'],
                                ['image' => 'img7.png', 'title' => 'Tactical Wear', 'desc' => 'Comfortable, rugged, stylish', 'price' => '₨1,500 – ₨3,500'],
                            ];
                        @endphp
                        @foreach ($products as $product)
                            <div class="col-lg-4 col-sm-6">
                                <div class="proBox">
                                    <figure><img src="{{ asset('assets/images/' . $product['image']) }}" class="img-fluid" alt="{{ $product['title'] }}"></figure>
                                    <h3>{{ $product['title'] }}</h3>
                                    <p>{{ $product['desc'] }}</p>
                                    <span>
                                        @for ($i = 0; $i < 5; $i++)
                                            <i class="fas fa-star"></i>
                                        @endfor
                                    </span>
                                    <ul>
                                        <li>{{ $product['price'] }}</li>
                                        <li><a href="{{ route('product.detail') }}"><i class="fal fa-arrow-right"></i></a></li>
                                    </ul>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Include Newsletter --}}
    @include('website.partials.newsletter')
@endsection