{{-- resources/views/website/find-a-dealer.blade.php --}}
@extends('website.layouts.main')

@section('title', 'Find a Licensed Firearms Dealer Near You | Guns & Wildlife')

@push('meta')
<meta name="description" content="Locate trusted Guns & Wildlife dealers across Pakistan. Buy legal firearms, tactical gear, and accessories from certified partners.">
@endpush

@section('content')
    <div class="main-slider banner">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="slideContent wow fadeInLeft" data-wow-delay="0.4s">
                        <h3>Find a</h3>
                        <h2>Dealer</h2>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="gunRghtimg wow fadeInRight" data-wow-delay="0.4s">
                    <figure><img src="{{ asset('assets/images/dealer.webp') }}" class="img-fluid" alt="img"></figure>
                    </div>
                </div>
            </div>
        </div>
        <h2 class="badgeHeading">Contact</h2>
    </div>

    <section class="storeLocator">
        <div class="container">
            <h2>STORE LOCATOR</h2>
            <div class="row">
                <div class="col-md-4">
                    <div class="storeBox wow fadeInLeft" data-wow-delay="0.4s">
                        <input type="text" name="location" placeholder="Current Location">
                        <label>Search Radius in Miles . mi</label>
                        <div>
                            <input type="range" min="0.111" max="3.33" step="0.111" value="1.776" data-rangeslider>
                            <output></output>
                        </div>
                        <a href="#" class="themeBtn">Locate Nearby</a>
                    </div>

                    @php
                        $dealers = [
                            [
                                'name' => '1791 Liberty Arms LLC',
                                'phone' => '',
                                'city' => 'Saratoga Springs',
                                'zip' => '84045',
                                'state' => 'Utah',
                                'address1' => '188 Summerhill DR, Saratoga',
                                'address2' => 'Springs, UT, 84045',
                            ],
                            [
                                'name' => '2223 Troy Ave.',
                                'phone' => '',
                                'city' => 'South El Monte',
                                'zip' => '91733',
                                'state' => 'California',
                                'address1' => '2223 troy ave, South El',
                                'address2' => 'Monte, CA, 91733',
                            ],
                        ];
                    @endphp

                    @foreach ($dealers as $dealer)
                        <div class="libertyBox">
                            <h3>{{ $dealer['name'] }}</h3>
                            <ul>
                                <li>Phone: {{ $dealer['phone'] ?: 'N/A' }}</li>
                                <li>City: {{ $dealer['city'] }}</li>
                                <li>Zip: {{ $dealer['zip'] }}</li>
                                <li>State: {{ $dealer['state'] }}</li>
                                <li>Address: {{ $dealer['address1'] }}</li>
                                <li>{{ $dealer['address2'] }}</li>
                            </ul>
                        </div>
                    @endforeach
                </div>
                <div class="col-md-8">
                    <div class="map wow fadeInRight" data-wow-delay="0.4s">
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d13004082.928417291!2d-104.65713107818928!3d37.275578278180674!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x54eab584e432360b%3A0x1c3bb99243deb742!2sUnited%20States!5e0!3m2!1sen!2s!4v1627572320101!5m2!1sen!2s"
                            width="100%" height="952" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection