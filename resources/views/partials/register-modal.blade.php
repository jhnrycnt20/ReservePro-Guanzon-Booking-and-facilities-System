<div class="modal fade" id="rpRegisterModal" tabindex="-1" aria-labelledby="rpRegisterModalLabel" aria-hidden="true" data-rp-autoshow="{{ $errors->register->any() ? '1' : '0' }}">
    <div class="modal-dialog modal-dialog-centered rp-login-reference-dialog">
        <div class="modal-content rp-login-modal rp-login-reference-modal">
            <div class="rp-login-reference-layout">
                <aside class="rp-login-reference-showcase" aria-hidden="true">
                    @include('partials.auth-showcase-slides')
                    <div class="rp-login-reference-showcase-copy">
                        <img src="{{ asset('images/guanzon_navbar_transparent.png') }}" alt="Guanzon Resort">
                    </div>
                </aside>
                <div class="rp-login-modal-body rp-login-reference-form-panel">
                <h2 class="rp-login-title" id="rpRegisterModalLabel">Sign up</h2>

                <div class="rp-register-notification" data-rp-register-notification data-rp-register-initial-notification="{{ session('error', '') }}" role="alert" aria-live="assertive" hidden>
                    <i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i>
                    <span data-rp-register-notification-message></span>
                    <button type="button" data-rp-register-notification-close aria-label="Dismiss notification">
                        <i class="bi bi-x-lg" aria-hidden="true"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('register') }}" autocomplete="off" data-rp-register-form novalidate>
                    @csrf
                    <div class="rp-register-step" data-rp-register-step="details">
                        <div class="rp-register-step-heading"><span class="rp-register-step-marker">1</span><span>Your details</span></div>
                    <div class="rp-login-field">
                        <input
                            id="rpRegisterName"
                            type="text"
                            class="@error('name', 'register') is-invalid @enderror"
                            name="name"
                            value="{{ old('name') }}"
                            autocomplete="off"
                            placeholder="Full name"
                            required
                            autofocus
                            data-rp-register-field="name"
                        >
                        <i class="bi bi-person rp-login-field-icon"></i>
                        <div class="rp-login-error" data-rp-register-feedback="name">@error('name', 'register'){{ $message }}@enderror</div>
                    </div>

                    <div class="rp-login-field">
                        <input
                            id="rpRegisterEmail"
                            type="email"
                            class="@error('email', 'register') is-invalid @enderror"
                            name="email"
                            value="{{ old('email') }}"
                            autocomplete="off"
                            placeholder="you@gmail.com"
                            required
                            data-rp-register-field="email"
                        >
                        <i class="bi bi-envelope rp-login-field-icon"></i>
                        <div class="rp-login-error @error('email', 'register') rp-register-inline-notice @enderror" data-rp-register-feedback="email">@error('email', 'register'){{ $message }}@enderror</div>
                    </div>

                    <div class="rp-login-field">
                        <input
                            id="rpRegisterPhone"
                            type="tel"
                            class="@error('phone', 'register') is-invalid @enderror"
                            name="phone"
                            value="{{ old('phone') }}"
                            autocomplete="off"
                            inputmode="numeric"
                            placeholder="Contact number (09171234567)"
                            required
                            data-rp-register-field="phone"
                        >
                        <i class="bi bi-telephone rp-login-field-icon"></i>
                        <div class="rp-login-error" data-rp-register-feedback="phone">@error('phone', 'register'){{ $message }}@enderror</div>
                    </div>

                    <button type="button" class="rp-register-next" data-rp-register-next>Continue</button>
                    </div>

                    <div class="rp-register-step" data-rp-register-step="password">
                        <button type="button" class="rp-register-step-heading rp-register-step-back" data-rp-register-back><span class="rp-register-step-marker"><i class="bi bi-check-lg" aria-hidden="true"></i></span><span data-rp-register-email-summary>Your details</span><i class="bi bi-pencil" aria-hidden="true"></i></button>
                        <div class="rp-register-step-heading is-current"><span class="rp-register-step-marker">2</span><span>Create password</span></div>
                    <div class="rp-login-field input-group">
                        <input
                            id="rpRegisterPassword"
                            type="password"
                            class="@error('password', 'register') is-invalid @enderror"
                            name="password"
                            autocomplete="off"
                            placeholder="Password"
                            required
                            data-rp-register-field="password"
                        >
                        <button type="button" class="rp-login-field-icon-btn" data-rp-toggle-password aria-label="Show password">
                            <i class="bi bi-eye"></i>
                        </button>
                        <div class="rp-login-error" data-rp-register-feedback="password">@error('password', 'register'){{ $message }}@enderror</div>
                    </div>

                    <ul class="rp-register-pw-checks list-unstyled" data-rp-register-pw-checks aria-live="polite">
                        <li data-rp-pw-rule="length"><i class="bi bi-circle"></i> At least 8 characters</li>
                        <li data-rp-pw-rule="upper"><i class="bi bi-circle"></i> One uppercase letter</li>
                        <li data-rp-pw-rule="lower"><i class="bi bi-circle"></i> One lowercase letter</li>
                        <li data-rp-pw-rule="number"><i class="bi bi-circle"></i> One number</li>
                    </ul>

                    <div class="rp-login-field input-group">
                        <input
                            id="rpRegisterPasswordConfirm"
                            type="password"
                            class="@error('password', 'register') is-invalid @enderror"
                            name="password_confirmation"
                            autocomplete="off"
                            placeholder="Confirm password"
                            required
                            data-rp-register-field="password_confirmation"
                        >
                        <button type="button" class="rp-login-field-icon-btn" data-rp-toggle-password aria-label="Show password">
                            <i class="bi bi-eye"></i>
                        </button>
                        <div class="rp-login-error" data-rp-register-feedback="password_confirmation"></div>
                    </div>

                    <button type="submit" class="rp-login-submit">Sign Up</button>
                    </div>
                </form>

                <div class="rp-login-signup">
                    Already have an account? <a href="{{ route('login') }}" data-bs-toggle="modal" data-bs-target="#rpLoginModal">Login</a>
                </div>
            </div>
            </div>
        </div>
    </div>
</div>
