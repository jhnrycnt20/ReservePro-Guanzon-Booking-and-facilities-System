@extends('layouts.public')

@section('title', 'Register')

@section('content')
<div class="container pt-5 pb-5" style="padding-top: 7rem;">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="rp-card">
                <a href="{{ route('login') }}" class="btn btn-rp-soft btn-sm mb-3">
                    <i class="bi bi-arrow-left me-1"></i> Back to login
                </a>
                <h1 class="h3 mb-1" style="font-family: var(--rp-display);">Create guest account</h1>
                <p class="text-muted mb-4">Register to browse and reserve accommodations</p>

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
@endsection
