<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Welcome') — {{ config('app.name', 'ReservePro') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Fraunces:opsz,wght@9..144,600;9..144,700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/reservepro.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="rp-public">
    <nav class="navbar navbar-expand-lg rp-public-nav">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ url('/') }}">
                <i class="bi bi-water me-1"></i> ReservePro
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#publicNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="publicNav">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                    <li class="nav-item"><a class="nav-link" href="{{ route('accommodations.browse') }}">Browse Resort</a></li>
                    @guest
                        <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Login</a></li>
                        <li class="nav-item"><a class="btn btn-rp-primary btn-sm" href="{{ route('register') }}">Register</a></li>
                    @else
                        <li class="nav-item"><a class="btn btn-rp-primary btn-sm" href="{{ auth()->user()->dashboardRoute() }}">My Dashboard</a></li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    @include('partials.alerts')
    @yield('content')

    <footer class="rp-footer">
        <div class="container py-4 d-flex flex-column flex-md-row justify-content-between gap-2">
            <div>&copy; {{ date('Y') }} ReservePro Resort Management</div>
            <div class="text-muted">Guanzon Booking & Facilities System</div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/reservepro.js') }}"></script>
    @stack('scripts')
</body>
</html>
