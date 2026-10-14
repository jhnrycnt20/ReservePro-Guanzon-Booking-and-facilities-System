@extends('layouts.public')

@section('title', 'Manage Profile')

@section('content')
<div class="container rp-public-page-top pb-4">
    <a href="{{ route('guest.bookings.index') }}" class="rp-back-link"><i class="bi bi-arrow-left"></i> Back to My Reservations</a>

    <div class="rp-page-intro">
        <h1 class="rp-page-intro-title">Manage Profile</h1>
        <p class="rp-page-intro-subtitle mb-0">Your account details</p>
    </div>

    <div class="rp-profile-page">
        @include('partials.profile-manage', [
            'profileUser' => $user,
            'useModalFlags' => false,
            'photoInputId' => 'guestProfileAvatar',
            'nameInputId' => 'guestProfileName',
            'emailInputId' => 'guestProfileEmail',
            'phoneInputId' => 'guestProfilePhone',
            'currentPasswordId' => 'guestProfileCurrentPassword',
            'newPasswordId' => 'guestProfileNewPassword',
            'confirmPasswordId' => 'guestProfileConfirmPassword',
        ])
    </div>
</div>
@endsection
