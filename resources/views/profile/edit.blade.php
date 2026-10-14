@extends('layouts.dashboard')

@php
    $roleSlug = $user->role?->slug;
    $sidebarPartial = match ($roleSlug) {
        'admin' => 'partials.sidebar-admin',
        'front_desk' => 'partials.sidebar-front-desk',
        'security' => 'partials.sidebar-security',
        default => 'partials.sidebar-guest',
    };
@endphp

@section('title', 'Manage Profile')
@section('theme', $roleSlug ?? 'guest')
@section('role_label', $user->role?->name ?? 'Dashboard')
@section('page_title', 'Manage Profile')
@section('sidebar')
    @include($sidebarPartial)
@endsection

@section('content')
<div class="rp-profile-page">
    @include('partials.profile-manage', [
        'profileUser' => $user,
        'useModalFlags' => false,
        'photoInputId' => 'pageProfileAvatar',
        'nameInputId' => 'profileName',
        'emailInputId' => 'profileEmail',
        'phoneInputId' => 'profilePhone',
        'currentPasswordId' => 'profileCurrentPassword',
        'newPasswordId' => 'profileNewPassword',
        'confirmPasswordId' => 'profileConfirmPassword',
    ])
</div>
@endsection
