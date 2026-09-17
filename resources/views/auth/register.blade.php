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

                    <form method="POST" action="{{ route('register') }}" data-rp-register-form novalidate>
                        @csrf
                        @if (session('error'))
                            <div class="alert alert-danger py-2" role="alert">{{ session('error') }}</div>
                        @endif
                        <div class="mb-3">
                            <label class="form-label" for="name">Full name</label>
                            <input
                                id="name"
                                type="text"
                                class="form-control @error('name', 'register') is-invalid @enderror"
                                name="name"
                                value="{{ old('name') }}"
                                autocomplete="name"
                                required
                                autofocus
                                data-rp-register-field="name"
                                placeholder="e.g. Juan Dela Cruz"
                            >
                            <div class="invalid-feedback" data-rp-register-feedback="name">
                                @error('name', 'register'){{ $message }}@enderror
                            </div>
                            <div class="form-text" data-rp-register-hint="name">Use your real name (letters only).</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="email">Email</label>
                            <input
                                id="email"
                                type="email"
                                class="form-control @error('email', 'register') is-invalid @enderror"
                                name="email"
                                value="{{ old('email') }}"
                                autocomplete="email"
                                required
                                data-rp-register-field="email"
                                placeholder="you@email.com"
                            >
                            <div class="invalid-feedback" data-rp-register-feedback="email">
                                @error('email', 'register'){{ $message }}@enderror
                            </div>
                            <div class="form-text" data-rp-register-hint="email">We’ll use this to sign you in.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="phone">Contact number</label>
                            <input
                                id="phone"
                                type="tel"
                                class="form-control @error('phone', 'register') is-invalid @enderror"
                                name="phone"
                                value="{{ old('phone') }}"
                                autocomplete="tel"
                                required
                                inputmode="numeric"
                                data-rp-register-field="phone"
                                placeholder="09171234567"
                            >
                            <div class="invalid-feedback" data-rp-register-feedback="phone">
                                @error('phone', 'register'){{ $message }}@enderror
                            </div>
                            <div class="form-text" data-rp-register-hint="phone">PH mobile: 09XXXXXXXXX or +639XXXXXXXXX.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="address">Address (optional)</label>
                            <textarea
                                id="address"
                                class="form-control @error('address', 'register') is-invalid @enderror"
                                name="address"
                                rows="2"
                                data-rp-register-field="address"
                                placeholder="City / province"
                            >{{ old('address') }}</textarea>
                            <div class="invalid-feedback" data-rp-register-feedback="address">
                                @error('address', 'register'){{ $message }}@enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="password">Password</label>
                            <input
                                id="password"
                                type="password"
                                class="form-control @error('password', 'register') is-invalid @enderror"
                                name="password"
                                autocomplete="new-password"
                                required
                                data-rp-register-field="password"
                            >
                            <div class="invalid-feedback" data-rp-register-feedback="password">
                                @error('password', 'register'){{ $message }}@enderror
                            </div>
                            <ul class="rp-register-pw-checks list-unstyled mb-0 mt-2" data-rp-register-pw-checks aria-live="polite">
                                <li data-rp-pw-rule="length"><i class="bi bi-circle"></i> At least 8 characters</li>
                                <li data-rp-pw-rule="upper"><i class="bi bi-circle"></i> One uppercase letter</li>
                                <li data-rp-pw-rule="lower"><i class="bi bi-circle"></i> One lowercase letter</li>
                                <li data-rp-pw-rule="number"><i class="bi bi-circle"></i> One number</li>
                            </ul>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="password-confirm">Confirm password</label>
                            <input
                                id="password-confirm"
                                type="password"
                                class="form-control @error('password', 'register') is-invalid @enderror"
                                name="password_confirmation"
                                autocomplete="new-password"
                                required
                                data-rp-register-field="password_confirmation"
                            >
                            <div class="invalid-feedback" data-rp-register-feedback="password_confirmation"></div>
                            <div class="form-text" data-rp-register-hint="password_confirmation">Re-enter the same password.</div>
                        </div>
                        <button type="submit" class="btn btn-rp-primary w-100">Register</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
