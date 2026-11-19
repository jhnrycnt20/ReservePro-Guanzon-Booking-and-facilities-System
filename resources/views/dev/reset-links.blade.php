<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Password reset links — local</title>
    <style>
        :root { color-scheme: dark; }
        body {
            margin: 0;
            font-family: Manrope, Segoe UI, sans-serif;
            background: #06140c;
            color: #e8f7ef;
        }
        main {
            width: min(720px, calc(100% - 2rem));
            margin: 2.5rem auto;
        }
        h1 { margin: 0 0 .35rem; font-size: 1.45rem; }
        p { margin: 0 0 1.4rem; color: rgba(232, 247, 239, .65); font-size: .9rem; }
        .card {
            margin-bottom: .85rem;
            padding: 1rem 1.1rem;
            border: 1px solid rgba(75, 170, 91, .28);
            border-radius: 14px;
            background: #0b1c12;
        }
        .meta { margin-bottom: .55rem; font-size: .78rem; color: rgba(232, 247, 239, .55); }
        a.link {
            display: block;
            word-break: break-all;
            color: #5ee1ba;
            font-size: .88rem;
            line-height: 1.45;
        }
        .empty {
            padding: 1.25rem;
            border: 1px dashed rgba(75, 170, 91, .35);
            border-radius: 14px;
            color: rgba(232, 247, 239, .7);
        }
        .actions { display: flex; gap: .6rem; margin-top: 1.2rem; }
        .actions a {
            color: #9fd9b9;
            font-size: .85rem;
        }
    </style>
</head>
<body>
<main>
    <h1>Password reset links</h1>
    <p>Local only. Open this after clicking “Send reset link” on the forgot-password form.</p>

    @forelse ($links as $link)
        <article class="card">
            <div class="meta">{{ $link['email'] }} · {{ $link['at'] }}</div>
            <a class="link" href="{{ $link['url'] }}">{{ $link['url'] }}</a>
        </article>
    @empty
        <div class="empty">No reset links yet. Request one from Login → Forgot Password.</div>
    @endforelse

    <div class="actions">
        <a href="{{ url('/?forgot=1') }}">Forgot password form</a>
        <a href="{{ url('/') }}">Home</a>
    </div>
</main>
</body>
</html>
