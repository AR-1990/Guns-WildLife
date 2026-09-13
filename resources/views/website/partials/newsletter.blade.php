<section class="newsLetter">
    <img src="{{ asset('assets/images/gun6.png') }}" class="img-fluid gun6 wow bounceIn" data-wow-delay="0.4s" alt="gun">
    <div class="container">
        <div class="row">
            <div class="col-md-12 wow fadeInUp" data-wow-delay="0.4s">
                <h2>SIGN UP FOR MY EMAIL NEWSLETTER</h2>
                <p>Get updates on new gear, deals & range events</p>
                @if (session('newsletter_success'))
                    <p class="text-success">{{ session('newsletter_success') }}</p>
                @endif
                <form action="{{ route('newsletter.subscribe') }}" method="POST">
                    @csrf
                    <input type="email" name="email" placeholder="Enter your email address ..." required>
                    <button type="submit" class="themeBtn">Subscribe Now</button>
                </form>
            </div>
        </div>
    </div>
    <img src="{{ asset('assets/images/gun7.png') }}" class="img-fluid gun7 wow bounceIn" data-wow-delay="0.8s" alt="gun">
</section>
