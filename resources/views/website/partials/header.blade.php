<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.png') }}" type="image/x-icon">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/fontawesome.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/animate.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/jquery.fancybox.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/slick.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/slick-theme.min.css') }}" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/rangeslider.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/custom.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/responsive.css') }}" />

    <meta name="keywords" content="guns in Pakistan, licensed firearms Pakistan, tactical gear, gun accessories, shooting training, gun repair, buy rifles Pakistan">
    <meta name="author" content="Guns & Wildlife">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://www.gunswildlife.pk/">
    <meta property="og:title" content="Buy Legal Firearms & Tactical Gear in Pakistan | Guns & Wildlife">
    <meta property="og:description" content="Shop legal weapons, tactical gear, accessories, and range services. Trusted arms provider across Pakistan.">
    <meta property="og:image" content="{{ asset('assets/images/img12.jpg') }}">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="https://www.gunswildlife.pk/">
    <meta name="twitter:title" content="Buy Legal Firearms & Tactical Gear in Pakistan | Guns & Wildlife">
    <meta name="twitter:description" content="Licensed guns, tactical gear, and accessories available online in Pakistan. Secure your gear with confidence.">
    <meta name="twitter:image" content="{{ asset('assets/images/img12.jpg') }}">

    <meta name="theme-color" content="#4c5324">
    <link rel="manifest" href="{{ asset('manifest.json') }}">

    {{-- Page specific title --}}
    <title>@yield('title', 'Guns & Wildlife')</title>
    @stack('meta')
    @stack('styles')
</head>
<body>
    {{-- Loader --}}
    <div class="an-loader">
        <img src="{{ asset('assets/images/logo.png') }}" alt="loading" />
    </div>

    {{-- Include Navigation --}}
    @include('website.partials.menu')
