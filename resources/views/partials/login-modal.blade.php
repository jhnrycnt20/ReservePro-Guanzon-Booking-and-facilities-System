<div class="modal fade" id="rpLoginModal" tabindex="-1" aria-labelledby="rpLoginModalLabel" aria-hidden="true" data-rp-autoshow="{{ $errors->login->any() ? '1' : '0' }}">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable rp-login-reference-dialog">
        <div class="modal-content rp-login-modal rp-login-reference-modal">
            <div class="rp-login-reference-layout">
                <aside class="rp-login-reference-showcase" aria-hidden="true">
                    @include('partials.auth-showcase-slides')
                    <div class="rp-login-reference-showcase-copy">
                        <img src="{{ asset('images/guanzon_navbar_transparent.png') }}" alt="Guanzon Resort">
                    </div>

                </aside>

                <div class="rp-login-modal-body rp-login-reference-form-panel">
                    <div class="rp-login-reference-heading">
                        <h2 class="rp-login-title" id="rpLoginModalLabel">Login</h2>
                    </div>

                    <form method="POST" action="{{ route('login') }}" autocomplete="off" data-rp-login-form novalidate>
                        @csrf
                        @if (session('success'))
                            <div class="rp-login-alert is-success" role="status">{{ session('success') }}</div>
                        @endif
                        @if (session('error'))
                            <div class="rp-login-alert" role="alert">{{ session('error') }}</div>
                        @endif

                        <div class="rp-login-field rp-login-reference-field">
                            <i class="bi bi-envelope rp-login-reference-leading-icon" aria-hidden="true"></i>
                            {{-- type=text + inputmode avoids some mobile browsers rejecting reserved .test demo emails --}}
                            <input id="rpLoginEmail" type="text" inputmode="email" autocomplete="off" class="@error('email', 'login') is-invalid @enderror" name="email" value="{{ old('email') }}" placeholder="Email" aria-describedby="rpLoginEmailError" @error('email', 'login') aria-invalid="true" @enderror required data-rp-login-field="email">
                            <div id="rpLoginEmailError" class="rp-login-error" role="status" aria-live="polite" data-rp-login-feedback="email">@error('email', 'login'){{ $message }}@enderror</div>
                        </div>

                        <div class="rp-login-field input-group rp-login-reference-field">
                            <i class="bi bi-lock rp-login-reference-leading-icon" aria-hidden="true"></i>
                            <input id="rpLoginPassword" type="password" class="@error('password', 'login') is-invalid @enderror" name="password" placeholder="Password" autocomplete="off" aria-describedby="rpLoginPasswordError" @error('password', 'login') aria-invalid="true" @enderror required data-rp-login-field="password">
                            <button type="button" class="rp-login-field-icon-btn" data-rp-toggle-password aria-label="Show password">
                                <i class="bi bi-eye"></i>
                            </button>
                            <div id="rpLoginPasswordError" class="rp-login-error" role="status" aria-live="polite" data-rp-login-feedback="password">@error('password', 'login'){{ $message }}@enderror</div>
                        </div>

                        <div class="rp-login-reference-options">
                            <div class="rp-login-remember">
                                <input type="checkbox" name="remember" id="rpLoginRemember" {{ old('remember') ? 'checked' : '' }}>
                                <label for="rpLoginRemember">Remember me</label>
                            </div>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="rp-login-reference-forgot" data-bs-toggle="modal" data-bs-target="#rpPasswordResetRequestModal" onclick="event.preventDefault()">Forgot Password?</a>
                            @endif
                        </div>

                        <button type="submit" class="rp-login-submit">
                            Login
                        </button>
                    </form>

                    <div class="rp-login-signup">
                        Don't have an account yet? <a href="{{ route('register') }}" data-bs-toggle="modal" data-bs-target="#rpRegisterModal">Sign Up</a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
