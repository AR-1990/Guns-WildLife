<!-- Begin: Footer -->
<footer>
    <div class="container">

        <div class="row">

            <div class="col-md-4 wow fadeInLeft" data-wow-delay="1.2s">
                <div class="ftrList">

                    <h3>Quick Links:</h3>

                    <ul>
                        <li><a href="{{ url('/') }}">Home</a></li>
                        <li><a href="{{ url('/about') }}">About Us</a></li>
                        <li><a href="{{ url('/shop') }}">Shop Now</a></li>
                        <li><a href="{{ url('/become-a-dealer') }}">Dealers</a></li>
                        <li><a href="{{ url('/contact') }}">Contact Us</a></li>
                    </ul>

                </div>
            </div>

            <div class="col-md-4 wow fadeInLeft" data-wow-delay="0.8s">

                <div class="ftrLogo">

                    <img src="{{ asset('assets/images/logo.png') }}"
                        class="img-fluid ftrLogo__img"
                        alt="logo">

                    <h3>Call Us :</h3>

                    <a href="tel:+11112223333">
                        📞 +111 222 3333
                    </a>

                    <a href="mailto:info@gunswildlife.pk">
                        📧 info@gunswildlife.pk
                    </a>

                    <h4>Follow Us</h4>

                    <ul>
                        <li>
                            <a href="#">
                                <img src="{{ asset('assets/images/fb.png') }}"
                                    class="img-fluid"
                                    alt="fb">
                            </a>
                        </li>

                        <li>
                            <a href="#">
                                <img src="{{ asset('assets/images/twtr.png') }}"
                                    class="img-fluid"
                                    alt="tw">
                            </a>
                        </li>

                        <li>
                            <a href="#">
                                <img src="{{ asset('assets/images/insta.png') }}"
                                    class="img-fluid"
                                    alt="ig">
                            </a>
                        </li>

                        <li>
                            <a href="#">
                                <img src="{{ asset('assets/images/linkdn.png') }}"
                                    class="img-fluid"
                                    alt="li">
                            </a>
                        </li>
                    </ul>

                </div>
            </div>

            <div class="col-md-4 wow fadeInLeft" data-wow-delay="0.4s">

                <div class="ftrList">

                    <h3>Categories:</h3>

                    <ul>
                        <li><a href="#">Complete Rifles</a></li>
                        <li><a href="#">Uppers</a></li>
                        <li><a href="#">Tactical Wear</a></li>
                    </ul>

                </div>
            </div>

        </div>

        <div class="row copyRight">
            <div class="col-md-12">
                <p>
                    © 2025 Guns & Wildlife.
                    All Rights Reserved.
                    <br>
                    Licensed Firearms | Tactical Gear | Pakistan
                </p>
            </div>
        </div>

    </div>
</footer>
<!-- END: Footer -->

<!-- Optional JavaScript -->
<script src="{{ asset('assets/js/jquery-3.5.1.min.js') }}"></script>
<script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/js/popper.min.js') }}"></script>

<script src="{{ asset('assets/js/lenis.min.js') }}"></script>

<script src="{{ asset('assets/js/jquery.fancybox.min.js') }}"></script>
<script src="{{ asset('assets/js/slick.min.js') }}"></script>
<script src="{{ asset('assets/js/rangeslider.js') }}"></script>

<script type="module"
    src="{{ asset('assets/js/model-viewer.min.js') }}">
</script>

<script src="{{ asset('assets/js/custom.min.js') }}"></script>

{{-- Additional Page Scripts --}}
@stack('scripts')
