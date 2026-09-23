<div class="modal fade" id="rpLoginModal" tabindex="-1" aria-labelledby="rpLoginModalLabel" aria-hidden="true" data-rp-autoshow="{{ $errors->login->any() ? '1' : '0' }}">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rp-login-modal">
            <button type="button" class="btn-close rp-login-modal-close" data-bs-dismiss="modal" aria-label="Close"></button>
            <div class="rp-login-modal-body">
                <h2 class="rp-login-title" id="rpLoginModalLabel">Login</h2>

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    @if (session('error'))
                        <div class="rp-login-alert" role="alert">{{ session('error') }}</div>
                    @endif

                    <div class="rp-login-field">
                        <input id="rpLoginEmail" type="email" class="@error('email', 'login') is-invalid @enderror" name="email" value="{{ old('email') }}" placeholder="Email" required autofocus>
                        <i class="bi bi-envelope rp-login-field-icon"></i>
                        <div class="rp-login-error">@error('email', 'login'){{ $message }}@enderror</div>
                    </div>

                    <div class="rp-login-field input-group">
                        <input id="rpLoginPassword" type="password" class="@error('password', 'login') is-invalid @enderror" name="password" placeholder="Password" required>
                        <button type="button" class="rp-login-field-icon-btn" data-rp-toggle-password aria-label="Show password">
                            <i class="bi bi-eye"></i>
                        </button>
                        <div class="rp-login-error">@error('password', 'login'){{ $message }}@enderror</div>
                    </div>

                    <div class="rp-login-remember">
                        <input type="checkbox" name="remember" id="rpLoginRemember" {{ old('remember') ? 'checked' : '' }}>
                        <label for="rpLoginRemember">Remember me</label>
                    </div>

                    <button type="submit" class="rp-login-submit">Login</button>
                </form>

                <div class="rp-login-signup">
                    Don't have an account? <a href="{{ route('register') }}" data-bs-toggle="modal" data-bs-target="#rpRegisterModal">Signup</a>
                </div>

                @if(config('demo.accounts'))
                    <div class="rp-login-demo">
                        <button type="button" class="rp-login-demo-head" data-bs-toggle="collapse" data-bs-target="#rpLoginDemoList" aria-expanded="false" aria-controls="rpLoginDemoList">
                            <span>Demo accounts</span>
                            <span class="rp-login-demo-head-right">
                                <span class="rp-login-demo-password">Password: {{ config('demo.password') }}</span>
                                <i class="bi bi-chevron-down rp-login-demo-chevron"></i>
                            </span>
                        </button>
                        <div class="collapse" id="rpLoginDemoList">
                            <div class="rp-login-demo-list">
                                @foreach(config('demo.accounts') as $account)
                                    <button
                                        type="button"
                                        class="rp-login-demo-btn"
                                        data-demo-email="{{ $account['email'] }}"
                                        data-demo-password="{{ config('demo.password') }}"
                                    >
                                        <span class="rp-login-demo-role">{{ $account['label'] }}</span>
                                        <span class="rp-login-demo-email">{{ $account['email'] }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
