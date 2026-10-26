@auth
@php $profileUser = auth()->user(); @endphp
<div class="modal fade" id="rpProfileModal" tabindex="-1" aria-labelledby="rpProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md modal-dialog-scrollable">
        <div class="modal-content rp-admin-modal rp-profile-modal">
            <div class="modal-header">
                <span class="rp-profile-modal-icon" aria-hidden="true"><i class="bi bi-person"></i></span>
                <div>
                    <h2 class="modal-title h5 mb-0" id="rpProfileModalLabel">Manage profile</h2>
                    <p class="rp-profile-modal-sub mb-0">Account details and settings</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @include('partials.profile-manage', [
                    'profileUser' => $profileUser,
                    'useModalFlags' => true,
                    'photoInputId' => 'rpProfileAvatar',
                    'nameInputId' => 'rpProfileName',
                    'emailInputId' => 'rpProfileEmail',
                    'phoneInputId' => 'rpProfilePhone',
                    'currentPasswordId' => 'rpProfileCurrentPassword',
                    'newPasswordId' => 'rpProfileNewPassword',
                    'confirmPasswordId' => 'rpProfileConfirmPassword',
                ])
            </div>
        </div>
    </div>
</div>
@endauth
