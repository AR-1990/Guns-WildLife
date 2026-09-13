@extends('website.layouts.main')

@section('title', 'FAQs – Legal Firearms, Licensing & Services | Guns & Wildlife Pakistan')

@push('meta')
<meta name="description" content="Get answers to common questions about gun licensing, purchases, services, and training. Trusted tactical gear & support for Pakistan.">
@endpush

@section('content')
    <div class="main-slider banner">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="slideContent">
                        <h3>Frequently Asked</h3>
                        <h2>Questions</h2>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="gunRghtimg">
                    <figure><img src="{{ asset('assets/images/dealer.webp') }}" class="img-fluid" alt="img"></figure>
                    </div>
                </div>
            </div>
        </div>
        <h2 class="badgeHeading">Contact</h2>
    </div>

    <section class="contctpg faqPage">
        <div class="container">
            <div class="cntct-head wow fadeInUp" data-wow-delay="0.4s">
                <h2>FORMS AND CONTACTS</h2>
            </div>
            <div class="row">
                <div class="col-lg-12 col-sm-12">
                    <div class="faq wow fadeInUp" data-wow-delay="0.4s">
                        <div id="accordion" class="accordionStyle">

                            @php
                                $faqs = [
                                    [
                                        'id' => 'One',
                                        'question' => 'Are your firearms legal in Pakistan?',
                                        'answer' => 'Yes, all firearms and tactical gear sold by Guns & Wildlife are fully licensed and compliant with Pakistani laws. We deal only in legal arms for civilians, professionals, and security agencies.',
                                        'show' => true
                                    ],
                                    [
                                        'id' => 'Two',
                                        'question' => 'Do I need a license to buy a weapon?',
                                        'answer' => 'Yes, a valid arms license is mandatory for purchasing firearms in Pakistan. You must present your original license and CNIC at the time of purchase.',
                                        'show' => false
                                    ],
                                    [
                                        'id' => 'Three',
                                        'question' => 'Do you provide shooting training?',
                                        'answer' => 'Absolutely. We offer certified shooting training programs led by experienced instructors at our indoor gun range. Sessions cover weapon handling, safety, and shooting accuracy.',
                                        'show' => false
                                    ],
                                    [
                                        'id' => 'Four',
                                        'question' => 'Can I place an order online?',
                                        'answer' => 'Yes, you can place orders for gear, accessories, and apparel directly through our website. However, firearms must be verified and collected in-store with proper documentation.',
                                        'show' => false
                                    ],
                                    [
                                        'id' => 'Five',
                                        'question' => 'Do you offer nationwide delivery?',
                                        'answer' => 'We deliver tactical gear, accessories, and clothing across Pakistan. Firearms and licensed items require in-person verification as per legal regulations.',
                                        'show' => false
                                    ],
                                    [
                                        'id' => 'Six',
                                        'question' => 'Can I get my weapon serviced or repaired?',
                                        'answer' => 'Yes, we offer professional weapon cleaning, repair, and upgrade services. You can book an appointment through our website or visit us directly.',
                                        'show' => false
                                    ],
                                    [
                                        'id' => 'Seven',
                                        'question' => 'How do I become a dealer or distributor?',
                                        'answer' => 'To become an authorized dealer, visit the "Become a Dealer" section on our site and fill out the application form. Our team will get in touch with you shortly.',
                                        'show' => false
                                    ],
                                ];
                            @endphp

                            @foreach ($faqs as $index => $faq)
                                <div class="card">
                                    <div class="card-header" id="heading{{ $faq['id'] }}">
                                        <button class="btn btn-link {{ !$faq['show'] ? 'collapsed' : '' }}" data-toggle="collapse"
                                            data-target="#collapse{{ $faq['id'] }}" aria-expanded="{{ $faq['show'] ? 'true' : 'false' }}"
                                            aria-controls="collapse{{ $faq['id'] }}">
                                            <span>{{ $index + 1 }}).</span> {{ $faq['question'] }}
                                            <i class="fas {{ $faq['show'] ? 'fa-minus' : 'fa-plus' }}"></i>
                                        </button>
                                    </div>
                                    <div id="collapse{{ $faq['id'] }}" class="collapse {{ $faq['show'] ? 'show' : '' }}"
                                        aria-labelledby="heading{{ $faq['id'] }}" data-parent="#accordion">
                                        <div class="card-body">
                                            <p>{{ $faq['answer'] }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection