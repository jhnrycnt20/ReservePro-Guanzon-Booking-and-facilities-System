<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 — {{ config('app.name', 'ReservePro') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&family=Cormorant+Garamond:wght@600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --rp-green: #0F6B45;
            --rp-green-dark: #0a4f33;
            --rp-ink: #2B4A3E;
            --rp-muted: #5f7b73;
            --rp-soft: #e8f7ef;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 1.5rem;
            font-family: "Plus Jakarta Sans", system-ui, sans-serif;
            color: var(--rp-ink);
            background:
                radial-gradient(circle at top left, rgba(111, 207, 151, .35), transparent 42%),
                radial-gradient(circle at bottom right, rgba(15, 107, 69, .18), transparent 46%),
                #f7fbf8;
        }
        .rp-403 {
            width: min(100%, 28rem);
            text-align: center;
            background: #fff;
            border: 1px solid #cfe5d9;
            border-radius: 1rem;
            padding: 2rem 1.5rem;
            box-shadow: 0 18px 40px rgba(15, 107, 69, .1);
        }
        .rp-403-code {
            font-family: "Cormorant Garamond", Georgia, "Times New Roman", serif;
            font-size: 3.5rem;
            line-height: 1;
            color: var(--rp-green);
            margin: 0 0 .5rem;
        }
        .rp-403 h1 {
            margin: 0 0 .75rem;
            font-size: 1.15rem;
            font-weight: 700;
        }
        .rp-403 p {
            margin: 0 0 1.5rem;
            color: var(--rp-muted);
            font-size: .95rem;
            line-height: 1.55;
        }
        .rp-403-actions {
            display: flex;
            flex-wrap: wrap;
            gap: .65rem;
            justify-content: center;
        }
        .rp-403-actions a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2.5rem;
            padding: .55rem 1.1rem;
            border-radius: .55rem;
            text-decoration: none;
            font-size: .88rem;
            font-weight: 600;
        }
        .rp-403-primary {
            background: linear-gradient(135deg, #6fcf97 0%, #0F6B45 100%);
            color: #fff;
        }
        .rp-403-secondary {
            background: var(--rp-soft);
            color: var(--rp-green-dark);
        }
    </style>
</head>
<body>
    @php
        $homeUrl = auth()->check()
            ? \App\Helpers\RoleRedirect::dashboardRoute()
            : route('home');
        $homeLabel = auth()->check() ? 'Back to my dashboard' : 'Back to home';
    @endphp
    <div class="rp-403">
        <div class="rp-403-code">403</div>
        <h1>Access restricted</h1>
        <p>{{ $exception->getMessage() ?: 'You do not have permission to open this page.' }}</p>
        <div class="rp-403-actions">
            <a class="rp-403-primary" href="{{ $homeUrl }}">{{ $homeLabel }}</a>
            @guest
                <a class="rp-403-secondary" href="{{ route('login') }}">Sign in</a>
            @endguest
        </div>
    </div>
</body>
</html>
