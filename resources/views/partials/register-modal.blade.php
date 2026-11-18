<div class="modal fade" id="rpRegisterModal" tabindex="-1" aria-labelledby="rpRegisterModalLabel" aria-hidden="true" data-rp-autoshow="{{ $errors->register->any() ? '1' : '0' }}">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable rp-login-reference-dialog">
        <div class="modal-content rp-login-modal rp-login-reference-modal">
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
                <h2 class="rp-login-title" id="rpRegisterModalLabel">Sign up</h2>

                <form method="POST" action="{{ route('register') }}" data-rp-register-form novalidate>
                    @csrf
                    @if (session('error'))
                        <div class="rp-login-alert" role="alert">{{ session('error') }}</div>
                    @endif
                    @if ($errors->register->any())
                        <div class="rp-login-alert" role="alert">
                            {{ $errors->register->first('email') ?: $errors->register->first() }}
                        </div>
                    @endif

                    <div class="rp-register-step" data-rp-register-step="details">
                        <div class="rp-register-step-heading"><span class="rp-register-step-marker">1</span><span>Your details</span></div>
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
                            placeholder="you@gmail.com"
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

                    <button type="button" class="rp-register-next" data-rp-register-next>Continue <i class="bi bi-arrow-right" aria-hidden="true"></i></button>
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
