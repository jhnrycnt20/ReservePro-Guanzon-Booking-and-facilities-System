@extends('layouts.public')

@section('title', 'Reset Password')
@section('body_class', 'rp-password-reset-route')

@section('content')
<main class="rp-password-reset-page">
    <div class="rp-password-reset-page-shell">
        <div class="rp-login-reference-layout rp-password-reset-page-layout">
            <aside class="rp-login-reference-showcase" aria-hidden="true">
                @include('partials.auth-showcase-slides')
                <div class="rp-login-reference-showcase-copy">
                    <img src="{{ asset('images/guanzon_navbar_transparent.png') }}" alt="Guanzon Resort">
                </div>
            </aside>

            <div class="rp-login-modal-body rp-login-reference-form-panel rp-password-reset-page-panel">
                <div class="rp-password-reset-heading">
                    <h1>Choose a new password</h1>
                    <p>Enter your account email and a new password to finish resetting access.</p>
                </div>

                <div class="rp-password-reset-step-heading">
                    <span class="rp-password-reset-step-marker"><i class="bi bi-shield-lock" aria-hidden="true"></i></span>
                    <span>New password</span>
                </div>

                <form method="POST" action="{{ route('password.update') }}" class="rp-password-reset-form rp-password-reset-page-form">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <label for="rpResetEmail">Email address</label>
                    <div class="rp-password-reset-field">
                        <i class="bi bi-envelope" aria-hidden="true"></i>
                        <input
                            id="rpResetEmail"
                            type="email"
                            name="email"
                            value="{{ $email ?? old('email') }}"
                            placeholder="Enter your email"
                            autocomplete="email"
                            autofocus
                            required
                            @error('email') aria-invalid="true" aria-describedby="rpResetEmailError" @enderror
                        >
                    </div>
                    @error('email')
                        <div class="rp-password-reset-error" id="rpResetEmailError" role="alert">{{ $message }}</div>
                    @enderror

                    <label for="rpResetPassword">New password</label>
                    <div class="rp-password-reset-field">
                        <i class="bi bi-lock" aria-hidden="true"></i>
                        <input
                            id="rpResetPassword"
                            type="password"
                            name="password"
                            placeholder="At least 8 characters"
                            autocomplete="new-password"
                            required
                            @error('password') aria-invalid="true" aria-describedby="rpResetPasswordError" @enderror
                        >
                        <button type="button" class="rp-login-field-icon-btn" data-rp-toggle-password aria-label="Show password">
                            <i class="bi bi-eye" aria-hidden="true"></i>
                        </button>
                    </div>
                    @error('password')
                        <div class="rp-password-reset-error" id="rpResetPasswordError" role="alert">{{ $message }}</div>
                    @enderror

                    <label for="rpResetPasswordConfirm">Confirm password</label>
                    <div class="rp-password-reset-field">
                        <i class="bi bi-lock-fill" aria-hidden="true"></i>
                        <input
                            id="rpResetPasswordConfirm"
                            type="password"
                            name="password_confirmation"
                            placeholder="Re-enter new password"
                            autocomplete="new-password"
                            required
                        >
                        <button type="button" class="rp-login-field-icon-btn" data-rp-toggle-password aria-label="Show password">
                            <i class="bi bi-eye" aria-hidden="true"></i>
                        </button>
                    </div>

                    <button type="submit" class="rp-password-reset-submit">
                        Reset password
                    </button>
                </form>

                <a href="{{ url('/?login=1') }}" class="rp-password-reset-back">
                    <i class="bi bi-arrow-left" aria-hidden="true"></i>
                    Back to login
                </a>
            </div>
        </div>
    </div>
</main>
@endsection
