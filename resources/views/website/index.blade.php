{{-- resources/views/home.blade.php --}}
@extends('website.layouts.main')

@section('title', 'Buy Legal Firearms & Tactical Gear in Pakistan | Guns & Wildlife')
@section('pageHeading', 'Home') {{-- Not strictly needed but good practice --}}

{{-- Push meta description --}}
@push('meta')
<meta name="description" content="Guns & Wildlife offers licensed weapons, gun accessories, and tactical gear across Pakistan. Explore secure shooting solutions, training, and repair services.">
@endpush

@section('content')
    @php
        $productTabs = [
            [
                'id' => 'all',
                'label' => 'All',
                'products' => [
                    ['image' => 'img4.png', 'title' => 'Complete Rifles', 'description' => 'Licensed weapons for civilian or defense use', 'price' => '₨9,500 - ₨10,500'],
                    ['image' => 'img5.png', 'title' => 'Caps & Tees', 'description' => 'Guns & Wildlife branded apparel', 'price' => '₨1,500 - ₨3,500'],
                    ['image' => 'img6.png', 'title' => 'Parts & Accessories', 'description' => 'Barrels, mags, triggers & more', 'price' => '₨9,500 - ₨10,500'],
                    ['image' => 'img7.png', 'title' => 'Tactical Wear', 'description' => 'Comfortable, rugged, stylish', 'price' => '₨1,500 - ₨3,500'],
                ],
            ],
            [
                'id' => 'rifles',
                'label' => 'Complete Rifles',
                'products' => [
                    ['image' => 'img4.png', 'title' => 'AR Platform Rifle', 'description' => 'Reliable range and field-ready rifle platform', 'price' => '₨12,500 - ₨18,000'],
                    ['image' => 'img6.png', 'title' => 'Bolt Action Rifle', 'description' => 'Precision-focused option for hunting and sport', 'price' => '₨15,500 - ₨21,000'],
                    ['image' => 'img12.jpg', 'title' => 'Tactical Carbine', 'description' => 'Compact tactical setup with enhanced control', 'price' => '₨13,500 - ₨19,500'],
                    ['image' => 'img13.webp', 'title' => 'Premium Rifle Kit', 'description' => 'Performance rifle package with accessories', 'price' => '₨18,500 - ₨25,000'],
                ],
            ],
            [
                'id' => 'lowers',
                'label' => 'Lowers',
                'products' => [
                    ['image' => 'img6.png', 'title' => 'Forged Lower', 'description' => 'Durable receiver built for long-term use', 'price' => '₨7,500 - ₨9,500'],
                    ['image' => 'img7.png', 'title' => 'Ambi Lower', 'description' => 'Enhanced ergonomics with ambidextrous control', 'price' => '₨8,500 - ₨11,500'],
                    ['image' => 'img8.png', 'title' => 'Builder Lower', 'description' => 'Ideal starting point for custom builds', 'price' => '₨6,500 - ₨8,500'],
                    ['image' => 'img9.png', 'title' => 'Range Lower', 'description' => 'Stable performance for frequent range sessions', 'price' => '₨7,000 - ₨9,000'],
                ],
            ],
            [
                'id' => 'uppers',
                'label' => 'Uppers',
                'products' => [
                    ['image' => 'img10.png', 'title' => 'Carbine Upper', 'description' => 'Balanced upper for speed, control and accuracy', 'price' => '₨10,500 - ₨14,500'],
                    ['image' => 'img11.png', 'title' => 'Precision Upper', 'description' => 'Extended performance upper for distance work', 'price' => '₨12,000 - ₨16,500'],
                    ['image' => 'img66.png', 'title' => 'Tactical Upper', 'description' => 'Accessory-ready configuration for tactical setups', 'price' => '₨11,500 - ₨15,000'],
                    ['image' => 'img88.png', 'title' => 'Lightweight Upper', 'description' => 'Reduced-weight option for mobile shooting', 'price' => '₨10,000 - ₨13,500'],
                ],
            ],
        ];

        $packages = [
            ['title' => 'Basic Arms', 'price' => '₨700 - ₨2,500', 'features' => ['Firearms only', 'Basic safety check', 'Entry-level option']],
            ['title' => 'Range Starter', 'price' => '₨3,500 - ₨6,500', 'features' => ['Firearm + ammo', 'Range lane booking', 'Safety assistance']],
            ['title' => 'Tactical Pro', 'price' => '₨7,500 - ₨12,500', 'features' => ['Tactical gear set', 'Maintenance support', 'Priority service']],
            ['title' => 'Dealer Bundle', 'price' => '₨15,000+', 'features' => ['Bulk product support', 'Partner pricing', 'Dedicated assistance']],
        ];
    @endphp

    <section class="main-slider">
        <div id="carouselExampleControls" class="carousel slide" data-ride="carousel">
            <div class="buletsOne">
                <ol class="carousel-indicators">
                    <li data-target="#carouselExampleControls" data-slide-to="0" class="active"></li>
                </ol>
            </div>
            <div class="carousel-inner">
                <div class="carousel-item active wow fadeInLeft" data-wow-delay="0.5s">
                    <div class="container">
                        <div class="row align-items-center">
                            <div class="col-md-5">
                                <div class="slideContent wow fadeInLeft" data-wow-delay="0.4s">
                                    <h3>WELCOME TO</h3>
                                    <h2>GUNS & WILDLIFE</h2>
                                    <p>Legally authorized firearms & tactical equipment for shooting, security &
                                        training.</p>
                                    <a href="{{ url('/shop') }}" class="themeBtn">Shop Now</a>
                                </div>
                            </div>
                            <div class="col-md-7">
                                <div class="gunRghtimg">
                                    <img src="{{ asset('assets/images/gun1.png') }}" class="img-fluid"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <h2 class="badgeHeading">Guns & WildLife</h2>
            <div class="container">
                <div class="row align-items-end topSet">
                    <div class="col-md-6">
                        <div class="gunRange wow fadeInUp" data-wow-delay="0.8s">
                            <img src="{{ asset('assets/images/img1.jpeg') }}" class="img-fluid" alt="img">
                            <div class="gunContent">
                                <h3>GUN RANGE</h3>
                                <p>Professional indoor shooting range safety, accuracy & skill enhancement under expert
                                    supervision.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="stateList wow fadeInRight" data-wow-delay="0.4s">
                            <span>
                                <ul>
                                    <li><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                                    <li><a href="#"><i class="fab fa-twitter"></i></a></li>
                                    <li><a href="#"><i class="fab fa-instagram"></i></a></li>
                                </ul>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="aboutSec">
        <img src="{{ asset('assets/images/gun2.png') }}" class="img-fluid gun1 wow bounceIn" data-wow-delay="0.4s" alt="img">
        <img src="{{ asset('assets/images/gun3.png') }}" class="img-fluid gun2 wow bounceIn" data-wow-delay="0.4s" alt="img">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="abtimg wow fadeInLeft" data-wow-delay="0.4s">
                        <img src="{{ asset('assets/images/img20.jpg') }}" class="" alt="img">
                        <figure><img src="{{ asset('assets/images/img3.jpg') }}" class="img-fluid" alt="img"></figure>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="abtContent wow fadeInRight" data-wow-delay="0.4s">
                        <h3>About Us</h3>
                        <h2>GUNS & WILDLIFE</h2>
                        <p>
                            We are Pakistan’s trusted name in firearms, tactical gear & shooting accessories.
                            Our expert-led training ensures safe handling & precision.
                        </p>
                        <p>
                            Shop with us for high-quality, licensed weapons and accessories.
                        </p>
                        <a href="#" class="themeBtn">Start Training</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="ServiceSec">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h2 class="sectionHeading">SERVICES</h2>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="servicebox">
                        <div class="boxService">
                            <img src="{{ asset('assets/images/service1.png') }}" alt="">
                            <h6>CLEANING</h6>
                            <h5>₨5,000</h5>
                            <p>Keep your weapon spotless</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="servicebox">
                        <div class="boxService">
                            <img src="{{ asset('assets/images/service2.png') }}" alt="">
                            <h6>REPAIR</h6>
                            <h5>₨5,000+Parts</h5>
                            <p>Efficient fix by experts</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="servicebox">
                        <div class="boxService">
                            <img src="{{ asset('assets/images/service3.png') }}" alt="">
                            <h6>UPGRADE</h6>
                            <h5>₨5,000+Parts</h5>
                            <p>Customize to your needs</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="servicebox">
                        <div class="boxService">
                            <img src="{{ asset('assets/images/service4.png') }}" alt="">
                            <h6>SHOOTING RANGE</h6>
                            <h5>₨2,000</h5>
                            <p>Skill test in safe setup</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <img src="{{ asset('assets/images/img9.png') }}" class="img-fluid" />
        
    </section>
    <section class="ourProducts">
        <div class="container">
            <div class="proHead wow fadeInUp" data-wow-delay="0.4s">
                <h2 class="sectionHeading">OUR PRODUCTS</h2>
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    @foreach ($productTabs as $index => $tab)
                        <li class="nav-item">
                            <a class="nav-link {{ $index === 0 ? 'active' : '' }}" id="{{ $tab['id'] }}-tab" data-toggle="tab"
                                href="#{{ $tab['id'] }}" role="tab" aria-controls="{{ $tab['id'] }}"
                                aria-selected="{{ $index === 0 ? 'true' : 'false' }}">
                                {{ $tab['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="tab-content wow fadeInUp" data-wow-delay="0.8s" id="myTabContent">
                @foreach ($productTabs as $index => $tab)
                    <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" id="{{ $tab['id'] }}"
                        role="tabpanel" aria-labelledby="{{ $tab['id'] }}-tab">
                        <div class="row">
                            @foreach ($tab['products'] as $product)
                                <div class="col-lg-3 col-sm-6">
                                    <div class="proBox">
                                        <figure><img src="{{ asset('assets/images/' . $product['image']) }}" class="img-fluid" alt="{{ $product['title'] }}"></figure>
                                        <h3>{{ $product['title'] }}</h3>
                                        <p>{{ $product['description'] }}</p>
                                        <span>
                                            @for ($star = 0; $star < 5; $star++)
                                                <i class="fas fa-star"></i>
                                            @endfor
                                        </span>
                                        <ul>
                                            <li>{{ $product['price'] }}</li>
                                            <li><a href="{{ url('/product-detail') }}"><i class="fal fa-arrow-right"></i></a></li>
                                        </ul>
                                    </div>
                                </div>
                            @endforeach
                            <div class="col-md-12">
                                <div class="viewBtn">
                                    <a href="{{ url('/shop') }}" class="themeBtn">View All</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <img src="{{ asset('assets/images/gun4.png') }}" class="img-fluid gun4 wow bounceIn" data-wow-delay="0.4s" alt="img">
        
    </section>
    <section class="PackageSec">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h2 class="sectionHeading">PACKAGES</h2>
                </div>
                @foreach ($packages as $package)
                    <div class="col-lg-3 col-sm-6">
                        <div class="packageBox">
                            <div class="boxService">
                                <h6>{{ strtoupper($package['title']) }}</h6>
                                <h5>{{ $package['price'] }}</h5>
                                <ul>
                                    @foreach ($package['features'] as $feature)
                                        <li><p>{{ $feature }}</p></li>
                                    @endforeach
                                </ul>
                                <a href="{{ url('/contact') }}" class="themeBtn">Buy Now</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    <section class="machineGun">
        <div class="container-fluid p-0">
            <div class="col-md-12">
                <h2 class="sectionHeading text-center">CATEGORIES</h2>
            </div>
            <div class="row no-gutters">
                <div class="col-sm-6 wow fadeInLeft" data-wow-delay="0.4s">
                    <div class="rifleImg">
                        <img src="{{ asset('assets/images/img12.jpg') }}" class="img-fluid" alt="img">
                        <div class="overlay">
                            <h2>COMPLETE RIFLES</h2>
                            <p>Pakistan-legal semi-auto & bolt-action rifles</p>
                            <a href="{{ url('/shop') }}" class="themeBtn">Shop Now</a>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="row no-gutters">
                        <div class="col-sm-12 wow fadeInRight" data-wow-delay="0.8s">
                            <div class="rifleImg rifleImgone">
                                <img src="{{ asset('assets/images/img13.webp') }}" class="img-fluid" alt="img">
                                <div class="overlay">
                                    <h2>LOWERS</h2>
                                    <p>Licensed lower receivers – durable & certified</p>
                                    <a href="{{ url('/shop') }}" class="themeBtn">Shop Now</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 wow fadeInUp" data-wow-delay="1.2s">
                            <div class="rifleImg rifleImgtwo">
                                <img src="{{ asset('assets/images/newimage.jpg') }}" class="img-fluid" alt="img">
                                <div class="overlay">
                                    <h2>UPPERS</h2>
                                    <p>Precision-tested upper receivers for modular builds</p>
                                    <a href="{{ url('/shop') }}" class="themeBtn">Shop Now</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="becomeDealer">
        <img src="{{ asset('assets/images/gun5.png') }}" class="img-fluid gun5 wow bounceIn" data-wow-delay="0.4s" alt="img">
        <div class="container">
            <div class="viewBtn wow fadeInUp" data-wow-delay="2s">
                <a href="{{ url('/shop') }}" class="themeBtn">View All</a>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <div class="dealrImg wow fadeInLeft" data-wow-delay="0.4s">
                        <figure><img src="{{ asset('assets/images/img18.jpg') }}" class="img-fluid" alt="img"></figure>
                        <a href="{{ url('/become-a-dealer') }}">BECOME A DEALER
                            <span class="d-block">Partner with Guns & Wildlife & grow your business</span>
                        </a>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="dealrImg wow fadeInRight" data-wow-delay="0.4s">
                        <figure><img src="{{ asset('assets/images/img19.jpg') }}" class="img-fluid" alt="img"></figure>
                        <a href="{{ url('/find-a-dealer') }}">FIND A DEALER
                            <span class="d-block">Locate authorized stores near you</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    
    @include('website.partials.newsletter')
@endsection
