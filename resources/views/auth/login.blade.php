@extends('layouts.public')

@section('title', 'Login')

@section('content')
<div class="container pt-5 pb-5" style="padding-top: 7rem;">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="rp-card">
                <h1 class="h3 mb-1" style="font-family: var(--rp-display);">Welcome back</h1>
                <p class="text-muted mb-4">Sign in to ReservePro</p>

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="email">Email</label>
                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autofocus>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="password">Password</label>
                        <div class="input-group">
                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required>
                            <button type="button" class="btn btn-outline-secondary" id="togglePassword" aria-label="Show password">
                                <i class="bi bi-eye" id="togglePasswordIcon"></i>
                            </button>
                        </div>
                        @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3 form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                    <button type="submit" class="btn btn-rp-primary w-100">Login</button>
                </form>

                <div class="mt-3 small text-muted">
                    No account? <a href="{{ route('register') }}">Register as guest</a>
                </div>

                @if(config('demo.accounts'))
                    <div class="rp-demo-accounts mt-4 pt-3 border-top">
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                            <div class="small fw-semibold text-uppercase" style="letter-spacing:.08em;">Demo accounts</div>
                            <span class="badge text-bg-light border">Password: {{ config('demo.password') }}</span>
                        </div>
                        <p class="small text-muted mb-3">Tap a role to fill the login form. All demo actors are seeded on Render.</p>
                        <div class="rp-demo-accounts-list">
                            @foreach(config('demo.accounts') as $account)
                                <button
                                    type="button"
                                    class="rp-demo-account-btn"
                                    data-demo-email="{{ $account['email'] }}"
                                    data-demo-password="{{ config('demo.password') }}"
                                >
                                    <span class="rp-demo-account-role">{{ $account['label'] }}</span>
                                    <span class="rp-demo-account-email">{{ $account['email'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
