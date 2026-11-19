<div
    class="modal fade"
    id="rpPasswordResetRequestModal"
    tabindex="-1"
    aria-labelledby="rpPasswordResetRequestModalLabel"
    aria-hidden="true"
    data-rp-autoshow="{{ request()->query('forgot') === '1' || old('_auth_form') === 'forgot' || session('password_reset_sent') ? '1' : '0' }}"
>
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable rp-login-reference-dialog rp-password-reset-dialog">
        <div class="modal-content rp-login-modal rp-login-reference-modal rp-password-reset-modal">
            <div class="rp-login-reference-layout">
                <aside class="rp-login-reference-showcase" aria-hidden="true">
                    @include('partials.auth-showcase-slides')
                    <div class="rp-login-reference-showcase-copy">
                        <img src="{{ asset('images/guanzon_navbar_transparent.png') }}" alt="Guanzon Resort">
                    </div>
                </aside>
                <div class="rp-login-modal-body rp-login-reference-form-panel">
                    <div class="rp-password-reset-heading">
                        <h2 id="rpPasswordResetRequestModalLabel">Reset your password</h2>
                        <p>Enter the email on your account and we’ll send a reset link.</p>
                    </div>

                    <div class="rp-password-reset-step-heading">
                        <span class="rp-password-reset-step-marker"><i class="bi bi-{{ session('status') ? 'check-lg' : 'envelope' }}" aria-hidden="true"></i></span>
                        <span>{{ session('status') ? 'Email sent' : 'Your email' }}</span>
                    </div>

                    @if (session('status'))
                        <div class="rp-password-reset-status" role="status">
                            <i class="bi bi-check-circle" aria-hidden="true"></i>
                            <span>
                                {{ session('status') }} Check your inbox and spam folder.
                                @if (app()->environment('local'))
                                    <br>
                                    <a href="{{ route('dev.reset-links') }}" style="color:#9ff0cb;text-decoration:underline;">Open reset links</a>
                                    (local: <code>/dev/reset-links</code>)
                                @endif
                            </span>
                        </div>
                    @endif

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
                            {{ session('status') ? 'Send another link' : 'Send reset link' }}
                        </button>
                    </form>

                    <button type="button" class="rp-password-reset-back" data-bs-toggle="modal" data-bs-target="#rpLoginModal">
                        <i class="bi bi-arrow-left" aria-hidden="true"></i>
                        Back to login
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
