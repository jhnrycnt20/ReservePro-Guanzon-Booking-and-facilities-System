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
                <div class="rp-contact-details mt-4">
                    <p class="mb-1">{{ $resortSettings['resort_address'] ?? 'Philippines' }}</p>
                    <p class="mb-2">
                        <a
                            class="rp-directions-link"
                            href="https://www.google.com/maps/dir/10.2039552,123.7581824/Guanzon+Beach+Resort,+6037+Langtad+Bridge,+Naga,+Cebu/@10.1789238,123.7298082,18.25z/data=!4m9!4m8!1m1!4e1!1m5!1m1!1s0x33a979114d9401e1:0x43fdbc208201cc90!2m2!1d123.729597!2d10.1789272?entry=ttu&g_ep=EgoyMDI2MDkxNS4wIKXMDSoASAFQAw%3D%3D"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            <i class="bi bi-send" aria-hidden="true"></i>
                            Directions
                        </a>
                    </p>
                    <p class="mb-1"><a href="mailto:{{ $resortSettings['resort_email'] ?? 'info@guanzonresort.com' }}">{{ $resortSettings['resort_email'] ?? 'info@guanzonresort.com' }}</a></p>
                    <p class="mb-0">{{ $resortSettings['resort_phone'] ?? '09190644054' }}</p>
                </div>
            </div>

            <div class="rp-contact-form-panel">
                <div class="rp-contact-form-panel-head">
                    <h2>Send Us Message</h2>
                </div>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show mx-3 mt-3" role="alert" data-rp-auto-dismiss>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show mx-3 mt-3" role="alert" data-rp-auto-dismiss>
                        <strong>Please fix the following:</strong>
                        <ul class="mb-0 mt-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ route('contact.store') }}" method="POST" class="rp-contact-form">
                    @csrf
                    <div class="row g-4">
                        <div class="col-md-6">
                            <input type="text" class="rp-contact-input @error('subject') is-invalid @enderror" name="subject" placeholder="Subject" value="{{ old('subject') }}">
                            @error('subject')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <input type="text" class="rp-contact-input @error('name') is-invalid @enderror" name="name" placeholder="Your Name" value="{{ old('name') }}" required>
                            @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <input type="email" class="rp-contact-input @error('email') is-invalid @enderror" name="email" placeholder="Email Address" value="{{ old('email') }}" required>
                            @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <input type="text" class="rp-contact-input @error('phone') is-invalid @enderror" name="phone" placeholder="Phone" value="{{ old('phone') }}">
                            @error('phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <textarea class="rp-contact-input @error('message') is-invalid @enderror" name="message" rows="4" placeholder="Message" required>{{ old('message') }}</textarea>
                            @error('message')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <button type="submit" class="rp-contact-submit">Send Message</button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
