@extends('layouts.public')

@section('title', 'Contact')

@section('content')
<section class="rp-hero">
    <div class="rp-hero-full">
        <div class="rp-hero-inner">
            <h1><span>CONTACT</span></h1>
            <div class="rp-hero-subtext">Guanzon Resort</div>
        </div>
    </div>
    <div class="rp-hero-scroll">
        <span class="rp-hero-scroll-line"></span>
        <span class="rp-hero-scroll-chevrons">
            <i class="bi bi-chevron-down"></i>
            <i class="bi bi-chevron-down"></i>
        </span>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="rp-contact-panels">
            <div class="rp-contact-lead">
                <h1 class="rp-contact-heading">Let us know what your ideal Guanzon getaway is like.</h1>
            </div>

            <div class="rp-contact-form-panel">
                <div class="rp-contact-form-panel-head">
                    <h2>Send Us Message</h2>
                </div>
                <form action="{{ route('contact.store') }}" method="POST" class="rp-contact-form">
                    @csrf
                    <div class="row g-4">
                        <div class="col-md-6">
                            <input type="text" class="rp-contact-input" name="subject" placeholder="Subject" value="{{ old('subject') }}">
                        </div>
                        <div class="col-md-6">
                            <input type="text" class="rp-contact-input" name="name" placeholder="Your Name" value="{{ old('name') }}" required>
                        </div>
                        <div class="col-md-6">
                            <input type="email" class="rp-contact-input" name="email" placeholder="Email Address" value="{{ old('email') }}" required>
                        </div>
                        <div class="col-md-6">
                            <input type="text" class="rp-contact-input" name="phone" placeholder="Phone" value="{{ old('phone') }}">
                        </div>
                        <div class="col-12">
                            <textarea class="rp-contact-input" name="message" rows="4" placeholder="Message" required>{{ old('message') }}</textarea>
                        </div>
                    </div>
                    <button type="submit" class="rp-contact-submit">Send Message</button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
