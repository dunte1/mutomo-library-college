<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#153168">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Offline | {{ config('app.name', 'OLLMCHS Library') }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            display: flex; align-items: center; justify-content: center;
            min-height: 100vh; margin: 0;
            background: #f5f7fa; color: #1e293b;
        }
        .card {
            text-align: center; padding: 2rem; max-width: 400px;
        }
        .icon { margin: 0 auto 1.5rem; width: 80px; height: 80px; }
        .icon svg { width: 100%; height: 100%; }
        h1 { font-size: 1.5rem; margin: 0 0 0.5rem; color: #153168; }
        p { color: #64748b; line-height: 1.6; }
        .btn {
            display: inline-block; margin-top: 1.5rem;
            padding: 0.75rem 2rem; border-radius: 0.5rem;
            background: #153168; color: #fff; text-decoration: none;
            font-weight: 600; font-size: 0.875rem;
        }
        .btn:hover { background: #1e4fa3; }
        @media (prefers-color-scheme: dark) {
            body { background: #0f172a; color: #e2e8f0; }
            h1 { color: #60a5fa; }
            p { color: #94a3b8; }
            .btn { background: #1e4fa3; }
            .btn:hover { background: #2563eb; }
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
        </div>
        <h1>You're offline</h1>
        <p>Connect to the internet to access the {{ config('app.name') }} system.</p>
        <a class="btn" href="javascript:window.location.reload()">Retry</a>
        <br>
        <a class="btn" href="javascript:(async()=>{if('serviceWorker'in navigator){const reg=await navigator.serviceWorker.getRegistration();if(reg)await reg.unregister()}window.location.reload()})()" style="background:#64748b;margin-top:0.5rem">Clear &amp; Retry</a>
    </div>
    <script>
        if (navigator.onLine) {
            (async function() {
                const reg = await navigator.serviceWorker.getRegistration();
                if (reg) await reg.unregister();
                window.location.reload();
            })();
        }
    </script>
</body>
</html>
