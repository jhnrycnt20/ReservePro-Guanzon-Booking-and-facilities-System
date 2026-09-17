<div class="modal fade" id="rpRegisterModal" tabindex="-1" aria-labelledby="rpRegisterModalLabel" aria-hidden="true" data-rp-autoshow="{{ $errors->register->any() ? '1' : '0' }}">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rp-login-modal">
            <button type="button" class="btn-close rp-login-modal-close" data-bs-dismiss="modal" aria-label="Close"></button>
            <div class="rp-login-modal-body">
                <h2 class="rp-login-title" id="rpRegisterModalLabel">Sign up</h2>

                <form method="POST" action="{{ route('register') }}" data-rp-register-form novalidate>
                    @csrf
                    @if (session('error'))
                        <div class="rp-login-alert" role="alert">{{ session('error') }}</div>
                    @endif

                    <div class="rp-login-field">
                        <input
                            id="rpRegisterName"
                            type="text"
                            class="@error('name', 'register') is-invalid @enderror"
                            name="name"
                            value="{{ old('name') }}"
                            autocomplete="name"
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
                            autocomplete="email"
                            placeholder="Email"
                            required
                            data-rp-register-field="email"
                        >
                        <i class="bi bi-envelope rp-login-field-icon"></i>
                        <div class="rp-login-error" data-rp-register-feedback="email">@error('email', 'register'){{ $message }}@enderror</div>
                    </div>

                    <div class="rp-login-field">
                        <input
                            id="rpRegisterPhone"
                            type="tel"
                            class="@error('phone', 'register') is-invalid @enderror"
                            name="phone"
                            value="{{ old('phone') }}"
                            autocomplete="tel"
                            inputmode="numeric"
                            placeholder="Contact number (09171234567)"
                            required
                            data-rp-register-field="phone"
                        >
                        <i class="bi bi-telephone rp-login-field-icon"></i>
                        <div class="rp-login-error" data-rp-register-feedback="phone">@error('phone', 'register'){{ $message }}@enderror</div>
                    </div>

                    <div class="rp-login-field input-group">
                        <input
                            id="rpRegisterPassword"
                            type="password"
                            class="@error('password', 'register') is-invalid @enderror"
                            name="password"
                            autocomplete="new-password"
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
                            autocomplete="new-password"
                            placeholder="Confirm password"
                            required
                            data-rp-register-field="password_confirmation"
                        >
                        <button type="button" class="rp-login-field-icon-btn" data-rp-toggle-password aria-label="Show password">
                            <i class="bi bi-eye"></i>
                        </button>
                        <div class="rp-login-error" data-rp-register-feedback="password_confirmation"></div>
                    </div>

                    <button type="submit" class="rp-login-submit">Register</button>
                </form>

                <div class="rp-login-signup">
                    Already have an account? <a href="{{ route('login') }}" data-bs-toggle="modal" data-bs-target="#rpLoginModal">Login</a>
                </div>
            </div>
        </div>
    </div>
</div>
