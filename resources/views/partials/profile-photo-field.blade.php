@php
    $profilePhotoUser = $profilePhotoUser ?? auth()->user();
    $photoInputId = $photoInputId ?? 'rpProfileAvatar';
    $photoLayout = $photoLayout ?? 'default'; // default | centered
    $initial = strtoupper(mb_substr($profilePhotoUser->name ?? 'U', 0, 1));
    $hasAvatar = filled($profilePhotoUser->avatar_url);
@endphp
<div class="rp-profile-photo{{ $photoLayout === 'centered' ? ' is-centered' : '' }}" data-rp-profile-photo>
    <div class="rp-profile-photo-preview{{ $photoLayout === 'centered' ? ' is-lg' : '' }}">
        <button
            type="button"
            class="rp-profile-photo-hit{{ $hasAvatar ? '' : ' is-empty' }}"
            data-rp-avatar-view
            @if($hasAvatar) data-rp-avatar-src="{{ $profilePhotoUser->avatar_url }}" @endif
            aria-label="{{ $hasAvatar ? 'View profile photo' : 'No profile photo yet' }}"
            @if(! $hasAvatar) disabled @endif
        >
            @if($hasAvatar)
                <img src="{{ $profilePhotoUser->avatar_url }}" alt="{{ $profilePhotoUser->name }}" data-rp-avatar-preview>
                <span class="rp-profile-avatar-fallback d-none" data-rp-avatar-fallback>{{ $initial }}</span>
            @else
                <img src="" alt="" class="d-none" data-rp-avatar-preview>
                <span class="rp-profile-avatar-fallback" data-rp-avatar-fallback>{{ $initial }}</span>
            @endif
        </button>
        <label class="rp-profile-photo-edit" for="{{ $photoInputId }}" title="Change photo">
            <i class="bi bi-camera" aria-hidden="true"></i>
            <span class="visually-hidden">Change photo</span>
        </label>
    </div>

    @if($photoLayout !== 'centered')
        <div class="rp-profile-photo-meta">
            <div class="rp-profile-identity-name">{{ $profilePhotoUser->name }}</div>
            <div class="rp-profile-identity-meta">{{ $profilePhotoUser->email }}</div>
            <div class="rp-profile-photo-actions">
                <label class="btn btn-rp-soft btn-sm mb-0" for="{{ $photoInputId }}">Upload photo</label>
                @if($profilePhotoUser->avatar)
                    <label class="rp-profile-photo-remove mb-0">
                        <input type="checkbox" name="remove_avatar" value="1" data-rp-avatar-remove>
                        Remove
                    </label>
                @endif
            </div>
            <div class="rp-profile-photo-hint">
                @if($hasAvatar)
                    Click photo to view · JPG, PNG, or WebP · max 2MB
                @else
                    JPG, PNG, or WebP · max 2MB
                @endif
            </div>
            @error('avatar', 'profile')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>
    @else
        <div class="rp-profile-photo-actions is-centered">
            @if($profilePhotoUser->avatar)
                <label class="rp-profile-photo-remove mb-0">
                    <input type="checkbox" name="remove_avatar" value="1" data-rp-avatar-remove>
                    Remove photo
                </label>
            @endif
        </div>
        <div class="rp-profile-photo-hint text-center">
            Tap camera to change · Click photo to view
        </div>
        @error('avatar', 'profile')
            <div class="text-danger small text-center mt-1">{{ $message }}</div>
        @enderror
    @endif

    <input
        id="{{ $photoInputId }}"
        type="file"
        name="avatar"
        class="d-none"
        accept="image/jpeg,image/png,image/webp"
        data-rp-avatar-input
    >
</div>
