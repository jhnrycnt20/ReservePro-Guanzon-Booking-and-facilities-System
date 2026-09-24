@extends('layouts.public')

@section('title', 'Manage Profile')

@section('content')
<div class="container rp-public-page-top pb-4">
    <a href="{{ route('guest.bookings.index') }}" class="rp-back-link"><i class="bi bi-arrow-left"></i> Back to My Reservations</a>

    <div class="rp-page-intro">
        <h1 class="rp-page-intro-title">Manage Profile</h1>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="rp-flow-card">
                <h3 class="h6 mb-3">Personal Information</h3>
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Full name</label>
                        <input type="text" name="name" class="form-control @error('name', 'profile') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                        @error('name', 'profile')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control @error('email', 'profile') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                        @error('email', 'profile')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contact number</label>
                        <input type="text" name="phone" class="form-control @error('phone', 'profile') is-invalid @enderror" value="{{ old('phone', $user->phone) }}" placeholder="09171234567" required>
                        @error('phone', 'profile')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="rp-avail-btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="rp-flow-card">
                <h3 class="h6 mb-3">Change Password</h3>
                <form method="POST" action="{{ route('profile.password') }}" data-rp-password-form novalidate>
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Current password</label>
                        <input type="password" name="current_password" class="form-control @error('current_password', 'password') is-invalid @enderror" required data-rp-password-field="current_password">
                        <div class="invalid-feedback @error('current_password', 'password') d-block @enderror" data-rp-password-feedback="current_password">@error('current_password', 'password'){{ $message }}@enderror</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New password</label>
                        <input type="password" name="password" class="form-control @error('password', 'password') is-invalid @enderror" required data-rp-password-field="password">
                        <div class="invalid-feedback @error('password', 'password') d-block @enderror" data-rp-password-feedback="password">@error('password', 'password'){{ $message }}@enderror</div>
                        <ul class="rp-register-pw-checks list-unstyled mb-0 mt-2" data-rp-password-pw-checks aria-live="polite">
                            <li data-rp-pw-rule="length"><i class="bi bi-circle"></i> At least 8 characters</li>
                            <li data-rp-pw-rule="upper"><i class="bi bi-circle"></i> One uppercase letter</li>
                            <li data-rp-pw-rule="lower"><i class="bi bi-circle"></i> One lowercase letter</li>
                            <li data-rp-pw-rule="number"><i class="bi bi-circle"></i> One number</li>
                        </ul>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm new password</label>
                        <input type="password" name="password_confirmation" class="form-control" required data-rp-password-field="password_confirmation">
                        <div class="invalid-feedback" data-rp-password-feedback="password_confirmation"></div>
                    </div>
                    <button type="submit" class="rp-avail-btn-secondary">Update Password</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
