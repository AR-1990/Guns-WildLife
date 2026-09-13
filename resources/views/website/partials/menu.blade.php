<!-- Begin: Header -->
<header class="wow fadeInDown" data-wow-delay="0.5s">
    <div class="main-navigate">
        <div class="an-navbar">
            <div class="container">
                <nav class="navbar navbar-expand-lg p-0">

                    <a class="navbar-brand" href="{{ url('/') }}">
                        <img src="{{ asset('assets/images/logo.png') }}" alt="Guns & Wildlife">
                    </a>

                    <button class="navbar-toggler" type="button"
                        data-toggle="collapse"
                        data-target="#navbarSupportedContent"
                        aria-controls="navbarSupportedContent"
                        aria-expanded="false"
                        aria-label="Toggle navigation">

                        <span class="far fa-bars"></span>
                    </button>

                    <div class="collapse navbar-collapse" id="navbarSupportedContent">

                        <ul class="navbar-nav mr-auto">

                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('/') ? 'active' : '' }}"
                                    href="{{ url('/') }}">
                                    Home
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('about') ? 'active' : '' }}"
                                    href="{{ url('/about') }}">
                                    About Us
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('shop') ? 'active' : '' }}"
                                    href="{{ url('/shop') }}">
                                    Shop Now
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('become-a-dealer') ? 'active' : '' }}"
                                    href="{{ url('/become-a-dealer') }}">
                                    Dealers
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('contact') ? 'active' : '' }}"
                                    href="{{ url('/contact') }}">
                                    Contact Us
                                </a>
                            </li>

                        </ul>

                        <div class="form-inline my-2 my-lg-0">
                            <ul>

                                <li>
                                    <a href="{{ url('/step-1.php') }}">
                                        <i class="fas fa-shopping-cart"></i>
                                    </a>
                                </li>

                                <li>
                                    <a href="{{ url('/wishlist') }}">
                                        <i class="fas fa-heart"></i>
                                    </a>
                                </li>

                                <li>
                                    <a href="{{ url('/account') }}">
                                        <i class="fas fa-user"></i>
                                    </a>
                                </li>

                            </ul>
                        </div>

                    </div>
                </nav>
            </div>
        </div>
    </div>
</header>
<!-- END: Header -->