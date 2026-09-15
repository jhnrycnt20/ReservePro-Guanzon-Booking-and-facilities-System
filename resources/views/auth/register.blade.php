@extends('layouts.public')

@section('title', 'Register')

@section('content')
<section class="rp-auth-hero">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-6">
                <div class="rp-auth-card">
                    <a href="{{ route('login') }}" class="rp-auth-back">
                        <i class="bi bi-arrow-left me-1"></i> Back to login
                    </a>

                    <div class="rp-auth-brand">
                        <img src="{{ asset('images/guanzon_logoW.png') }}" alt="Guanzon Resort">
                        <div>
                            <div class="rp-auth-kicker">Guanzon Resort</div>
                            <h1>Create guest account</h1>
                            <p>Register to browse and reserve accommodations.</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('register') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="name">Full name</label>
                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autofocus>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="email">Email</label>
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="phone">Contact number</label>
                            <input id="phone" type="text" class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone') }}" required>
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="address">Address (optional)</label>
                            <textarea id="address" class="form-control @error('address') is-invalid @enderror" name="address" rows="2">{{ old('address') }}</textarea>
                            @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="password">Password</label>
                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required>
                            <div class="form-text">At least 8 characters, with uppercase, lowercase, and a number.</div>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="password-confirm">Confirm password</label>
                            <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required>
                        </div>
                        <button type="submit" class="btn btn-rp-primary w-100">Register</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
