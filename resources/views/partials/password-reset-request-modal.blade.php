<div
    class="modal fade"
    id="rpPasswordResetRequestModal"
    tabindex="-1"
    aria-labelledby="rpPasswordResetRequestModalLabel"
    aria-hidden="true"
    data-rp-autoshow="{{ request()->routeIs('password.request') || old('_auth_form') === 'forgot' || session('status') ? '1' : '0' }}"
>
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable rp-login-reference-dialog rp-password-reset-dialog">
        <div class="modal-content rp-login-modal rp-login-reference-modal rp-password-reset-modal">
            <div class="rp-login-reference-layout">
                <aside class="rp-login-reference-showcase" aria-hidden="true">
                    <div class="rp-login-reference-slides">
                        <div class="rp-login-reference-slide" style="background-image: url('{{ asset('images/rooms/cottage/Sunlit Picnic Pavilion Courtyard.png') }}');"></div>
                        <div class="rp-login-reference-slide" style="background-image: url('{{ asset('images/rooms/cabana/Sunset Modern Bungalow Unit 4.png') }}');"></div>
                    </div>
                    <div class="rp-login-reference-showcase-copy">
                        <img src="{{ asset('images/guanzon_navbar_transparent.png') }}" alt="Guanzon Resort">
                    </div>
                </aside>
                <div class="rp-login-modal-body rp-login-reference-form-panel">

            <div class="rp-password-reset-heading">
                <h2 id="rpPasswordResetRequestModalLabel">Reset your password</h2>
            </div>

            <div class="rp-password-reset-step-heading">
                <span class="rp-password-reset-step-marker"><i class="bi bi-{{ session('status') ? 'check-lg' : 'envelope' }}" aria-hidden="true"></i></span>
                <span>{{ session('status') ? 'Email sent' : 'Your email' }}</span>
            </div>

            @if (session('status'))
                <div class="rp-password-reset-status" role="status">
                    <i class="bi bi-check-circle" aria-hidden="true"></i>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @unless (session('status'))
            <form method="POST" action="{{ route('password.email') }}" class="rp-password-reset-form">
                @csrf
                <input type="hidden" name="_auth_form" value="forgot">

                <label for="rpPasswordResetEmail">Email address</label>
                <div class="rp-password-reset-field">
                    <i class="bi bi-envelope" aria-hidden="true"></i>
                    <input
                        id="rpPasswordResetEmail"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="Enter your email"
                        autocomplete="email"
                        @error('email') aria-invalid="true" aria-describedby="rpPasswordResetEmailError" @enderror
                        required
                    >
                </div>
                @error('email')
                    <div class="rp-password-reset-error" id="rpPasswordResetEmailError" role="alert">{{ $message }}</div>
                @enderror

                <button type="submit" class="rp-password-reset-submit">
                    Send reset link
                </button>
            </form>
            @endunless

            <button type="button" class="rp-password-reset-back" data-bs-toggle="modal" data-bs-target="#rpLoginModal">
                <i class="bi bi-arrow-left" aria-hidden="true"></i>
                Back to login
            </button>
                </div>
            </div>
        </div>
    </div>
</div>
