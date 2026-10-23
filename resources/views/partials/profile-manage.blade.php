@php
    $profileUser = $profileUser ?? auth()->user();
    $photoInputId = $photoInputId ?? 'rpProfileAvatar';
    $nameInputId = $nameInputId ?? 'rpProfileName';
    $emailInputId = $emailInputId ?? 'rpProfileEmail';
    $phoneInputId = $phoneInputId ?? 'rpProfilePhone';
    $currentPasswordId = $currentPasswordId ?? 'rpProfileCurrentPassword';
    $newPasswordId = $newPasswordId ?? 'rpProfileNewPassword';
    $confirmPasswordId = $confirmPasswordId ?? 'rpProfileConfirmPassword';
    $useModalFlags = $useModalFlags ?? false;
    $initial = strtoupper(mb_substr($profileUser->name ?? 'U', 0, 1));
    $hasAvatar = filled($profileUser->avatar_url);
    $profileBag = $errors->getBag('profile');
    $passwordBag = $errors->getBag('password');
    $passwordErrors = $passwordBag->isNotEmpty()
        || $profileBag->hasAny(['current_password', 'password', 'password_confirmation']);
    $startInEdit = $profileBag->isNotEmpty()
        || $passwordBag->isNotEmpty()
        || old('_modal') === 'profile'
        || old('_modal') === 'profile_password';
@endphp

<div class="rp-profile-layout" data-rp-profile-manage @if($startInEdit) data-rp-profile-editing="1" @endif>
    <div class="rp-profile-view" data-rp-profile-view @if($startInEdit) hidden @endif>
        <div class="rp-profile-hero">
            <button
                type="button"
                class="rp-profile-photo-hit{{ $hasAvatar ? '' : ' is-empty' }} rp-profile-hero-avatar"
                data-rp-avatar-view
                @if($hasAvatar) data-rp-avatar-src="{{ $profileUser->avatar_url }}" @endif
                aria-label="{{ $hasAvatar ? 'View profile photo' : 'No profile photo yet' }}"
                @if(! $hasAvatar) disabled @endif
            >
                @if($hasAvatar)
                    <img src="{{ $profileUser->avatar_url }}" alt="{{ $profileUser->name }}">
                @else
                    <span class="rp-profile-avatar-fallback">{{ $initial }}</span>
                @endif
            </button>
            <div class="rp-profile-hero-text">
                <div class="rp-profile-hero-top">
                    <div class="rp-profile-identity-name">{{ $profileUser->name }}</div>
                    <button type="button" class="rp-profile-edit-link" data-rp-profile-edit>Edit profile</button>
                </div>
            </div>
        </div>

        <dl class="rp-profile-details">
            <div>
                <dt>Full name</dt>
                <dd>{{ $profileUser->name }}</dd>
            </div>
            <div>
                <dt>Email</dt>
                <dd>{{ $profileUser->email }}</dd>
            </div>
            <div>
                <dt>Contact number</dt>
                <dd>{{ $profileUser->phone ?: '—' }}</dd>
            </div>
        </dl>
    </div>

    <div class="rp-profile-edit" data-rp-profile-edit-panel @if(! $startInEdit) hidden @endif>
        <div class="rp-profile-edit-shell">
            <div class="rp-profile-edit-bar">
                <button type="button" class="rp-profile-back-btn" data-rp-profile-cancel aria-label="Back">
                    <i class="bi bi-arrow-left" aria-hidden="true"></i>
                </button>
                <h3 class="rp-profile-edit-title mb-0">Edit Profile</h3>
                <button type="submit" form="rpProfileInfoForm-{{ $photoInputId }}" class="rp-profile-save-icon" aria-label="Save changes" title="Save changes">
                    <i class="bi bi-check-lg" aria-hidden="true"></i>
                </button>
            </div>

            <form
                id="rpProfileInfoForm-{{ $photoInputId }}"
                method="POST"
                action="{{ route('profile.update') }}"
                class="rp-profile-form rp-profile-edit-form"
                enctype="multipart/form-data"
                data-rp-password-form
                novalidate
            >
                @csrf
                @method('PUT')
                @if($useModalFlags)
                    <input type="hidden" name="_modal" value="profile">
                @endif

                @include('partials.profile-photo-field', [
                    'profilePhotoUser' => $profileUser,
                    'photoInputId' => $photoInputId,
                    'photoLayout' => 'centered',
                ])

                <div class="rp-profile-stack">
                    <div>
                        <label class="form-label" for="{{ $nameInputId }}">Name</label>
                        <input id="{{ $nameInputId }}" type="text" name="name" class="form-control rp-profile-soft-input @error('name', 'profile') is-invalid @enderror" value="{{ old('name', $profileUser->name) }}" required>
                        @error('name', 'profile')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="form-label" for="{{ $emailInputId }}">Email address</label>
                        <input id="{{ $emailInputId }}" type="email" name="email" class="form-control rp-profile-soft-input @error('email', 'profile') is-invalid @enderror" value="{{ old('email', $profileUser->email) }}" required>
                        @error('email', 'profile')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="form-label" for="{{ $phoneInputId }}">Phone number</label>
                        <input id="{{ $phoneInputId }}" type="text" name="phone" class="form-control rp-profile-soft-input @error('phone', 'profile') is-invalid @enderror" value="{{ old('phone', $profileUser->phone) }}" placeholder="09171234567" required>
                        @error('phone', 'profile')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="rp-profile-pw-dropdown{{ $passwordErrors ? ' is-open' : '' }}" data-rp-password-dropdown>
                    <button
                        type="button"
                        class="rp-profile-pw-toggle"
                        data-rp-password-toggle
                        aria-expanded="{{ $passwordErrors ? 'true' : 'false' }}"
                    >
                        <span>Change password</span>
                        <i class="bi bi-chevron-down" aria-hidden="true"></i>
                    </button>
                    <div class="rp-profile-pw-panel" data-rp-password-panel @if(! $passwordErrors) hidden @endif>
                        <div class="rp-profile-stack">
                            <div>
                                <label class="form-label" for="{{ $currentPasswordId }}">Current password</label>
                                <input id="{{ $currentPasswordId }}" type="password" name="current_password" class="form-control rp-profile-soft-input @error('current_password', 'profile') is-invalid @enderror" data-rp-password-field="current_password" autocomplete="current-password">
                                <div class="invalid-feedback @error('current_password', 'profile') d-block @enderror" data-rp-password-feedback="current_password">@error('current_password', 'profile'){{ $message }}@enderror</div>
                            </div>
                            <div>
                                <label class="form-label" for="{{ $newPasswordId }}">New password</label>
                                <input id="{{ $newPasswordId }}" type="password" name="password" class="form-control rp-profile-soft-input @error('password', 'profile') is-invalid @enderror" data-rp-password-field="password" autocomplete="new-password">
                                <div class="invalid-feedback @error('password', 'profile') d-block @enderror" data-rp-password-feedback="password">@error('password', 'profile'){{ $message }}@enderror</div>
                            </div>
                            <div>
                                <label class="form-label" for="{{ $confirmPasswordId }}">Confirm password</label>
                                <input id="{{ $confirmPasswordId }}" type="password" name="password_confirmation" class="form-control rp-profile-soft-input" data-rp-password-field="password_confirmation" autocomplete="new-password">
                                <div class="invalid-feedback" data-rp-password-feedback="password_confirmation"></div>
                            </div>
                        </div>
                        <ul class="rp-profile-pw-checks list-unstyled mb-0" data-rp-password-pw-checks aria-live="polite">
                            <li data-rp-pw-rule="length"><i class="bi bi-check-circle" aria-hidden="true"></i><span>8+ characters</span></li>
                            <li data-rp-pw-rule="upper"><i class="bi bi-check-circle" aria-hidden="true"></i><span>Uppercase</span></li>
                            <li data-rp-pw-rule="lower"><i class="bi bi-check-circle" aria-hidden="true"></i><span>Lowercase</span></li>
                            <li data-rp-pw-rule="number"><i class="bi bi-check-circle" aria-hidden="true"></i><span>Number</span></li>
                        </ul>
                    </div>
                </div>

                <div class="rp-profile-actions d-md-none">
                    <button type="submit" class="btn btn-rp-primary w-100">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
