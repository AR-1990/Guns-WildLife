{{-- resources/views/website/about.blade.php --}}
@extends('website.layouts.main')

@section('title', 'About Guns & Wildlife – Pakistan\'s Trusted Tactical Firearms Store')

@push('meta')
<meta name="description" content="Learn more about Guns & Wildlife – your source for legal arms, range training, and expert support in Pakistan's tactical and defense market.">
@endpush

@section('content')
    <div class="main-slider banner">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="slideContent wow fadeInLeft" data-wow-delay="0.4s">
                        <h3>About</h3>
                        <h2>about us</h2>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="gunRghtimg wow fadeInRight" data-wow-delay="0.4s">
                        <figure><img src="{{ asset('assets/images/about.png') }}" class="img-fluid" alt="img"></figure> 
                        <!-- <model-viewer src="{{ asset('assets/images/Machine Gun.glb') }}" class="gun-3d" alt="A 3D model of a firearm"
                            ar ar-modes="webxr scene-viewer quick-look" environment-image="neutral" auto-rotate
                            disable-zoom camera-controls></model-viewer> -->
                    </div>
                </div>
            </div>
        </div>
        <h2 class="badgeHeading">about u</h2>
    </div>

    <section class="abtPage">
        <div class="container">
            <div class="row">
                <div class="col-md-7">
                    <div class="abtPgcontent wow fadeInLeft" data-wow-delay="0.4s">
                        <h3>About us</h3>
                        <h2>GUNS & WILDLIFE</h2>
                        <p>
                            At Guns & Wildlife, we specialize in legal firearms, tactical gear, and shooting solutions
                            across Pakistan. With a focus on safety, training, and quality, we aim to empower
                            individuals with the right tools and knowledge for protection, sport, and profession.
                        </p>
                        <p>
                            Our team comprises certified experts and trainers who ensure every product and service meets
                            legal standards and practical needs. Whether you are preparing for security duty, refining
                            your range skills, or simply investing in reliable gear we've got you covered.
                        </p>
                    </div>
                </div>
                <div class="col-md-5 wow fadeInRight" data-wow-delay="0.4s">
                    <img src="{{ asset('assets/images/img20.jpg') }}" class="img-fluid" alt="img">
                </div>
            </div>
            <div class="row">
                <div class="col-md-11">
                    <div class="abtPgcontent abtPgcontentwo abtContent wow fadeInUp" data-wow-delay="0.4s">
                        <p>
                            We take pride in offering an authentic and responsible platform where Pakistanis can
                            explore:
                        </p>
                        <ul class="mb-3">
                            <li>Licensed complete rifles and tactical kits</li>
                            <li>Professional-grade accessories and parts</li>
                            <li>Hands-on training and range services</li>
                            <li>After-sale support and custom upgrades</li>
                        </ul>
                        <p>
                            With customer satisfaction and firearm safety at the core of our mission, Guns & Wildlife
                            continues to set a new benchmark in Pakistan's tactical and defense gear industry.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="becomeDealer aboutDealer">        
        <div class="container">            
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