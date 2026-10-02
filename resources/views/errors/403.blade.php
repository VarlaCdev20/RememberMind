<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Acceso restringido · {{ config('app.name', 'RememberMind') }}</title>
    <style>
        :root { color-scheme: light; font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        * { box-sizing: border-box; }
        body { min-height: 100vh; margin: 0; display: grid; place-items: center; padding: 24px; background: #f5f7f3; color: #26352a; }
        main { width: min(100%, 480px); padding: clamp(28px, 6vw, 48px); border: 1px solid #dce5d9; border-radius: 24px; background: #fff; box-shadow: 0 18px 50px rgba(30, 52, 35, .08); }
        .brand { color: #4f7158; font-size: .8rem; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; }
        .icon { display: grid; place-items: center; width: 56px; height: 56px; margin-top: 32px; border-radius: 16px; background: #eaf2e9; color: #4f7158; }
        .code { margin: 28px 0 8px; color: #4f7158; font-size: .75rem; font-weight: 800; letter-spacing: .14em; text-transform: uppercase; }
        h1 { margin: 0; font-size: clamp(1.75rem, 5vw, 2.25rem); line-height: 1.2; }
        .message { margin: 16px 0 28px; color: #526158; line-height: 1.6; }
        .actions { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; }
        .button { display: inline-flex; align-items: center; justify-content: center; min-height: 44px; padding: 10px 20px; border: 1px solid #47684f; border-radius: 999px; background: #47684f; color: #fff; font: inherit; font-size: .9rem; font-weight: 700; text-decoration: none; cursor: pointer; }
        .button:hover, .button:focus-visible { background: #36543d; }
        .button.secondary { border-color: #d5dfd3; background: #fff; color: #36543d; }
        .button.secondary:hover, .button.secondary:focus-visible { background: #f2f6f1; }
        .button:focus-visible { outline: 3px solid #9abe9d; outline-offset: 3px; }
        form { margin: 0; }
    </style>
</head>
<body>
    <main>
        <div class="brand">{{ config('app.name', 'RememberMind') }}</div>
        <div class="icon" aria-hidden="true">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z" />
                <path d="M9 12h6" />
            </svg>
        </div>
        <p class="code">Código 403</p>
        <h1>Acceso restringido</h1>
        <p class="message">No tienes acceso a esta sección con tu cuenta actual. Si necesitas ingresar, comunícate con la administración del centro.</p>
        <div class="actions">
            <a class="button" href="{{ route('welcome') }}">Ir al inicio</a>
            @auth
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="button secondary" type="submit">Cerrar sesión</button>
                </form>
            @endauth
        </div>
    </main>
</body>
</html>
