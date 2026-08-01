<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Library Card - {{ $member->full_name }}</title>
    <style>
        @import url('https://fonts.bunny.net/css2?family=Inter:wght@400;500;600;700&family=Montserrat:wght@700;800&family=Poppins:wght@600;700&display=swap');
        html, body {
            margin: 0;
            padding: 0;
            background: #f1f5f9;
        }
        body {
            font-family: 'Inter', system-ui, sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        #wrap {
            overflow: hidden;
            margin: 0 auto;
            width: 100%;
        }
        #stage {
            width: 1011px;
            transform-origin: top left;
        }
        .empty {
            min-height: 638px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 12px;
            background: #fff;
            border-radius: 14px;
            color: #5a6a82;
            font-size: 16px;
            margin: 16px;
        }
        .empty strong { color: #0a1e3a; font-size: 20px; }
    </style>
</head>
<body>
    @if($card)
        <div id="wrap">
            <div id="stage">
                @include('members::partials.library-card-face', [
                    'card' => $card,
                    'member' => $member,
                    'cardBranding' => $cardBranding,
                    'displaySettings' => $displaySettings,
                    'cardAuthority' => $cardAuthority,
                ])
            </div>
        </div>
        <script>
            (function () {
                var wrap = document.getElementById('wrap');
                var stage = document.getElementById('stage');

                function fit() {
                    var available = window.innerWidth - 24;
                    var scale = Math.min(1, available / 1011);
                    scale = Math.max(scale, 0.2);
                    stage.style.transform = 'scale(' + scale + ')';
                    wrap.style.width = (1011 * scale) + 'px';
                    wrap.style.height = (638 * scale) + 'px';
                }

                window.addEventListener('resize', fit);
                window.addEventListener('orientationchange', fit);
                window.addEventListener('load', fit);
                fit();
            })();
        </script>
    @else
        <div class="empty">
            <strong>No Library Card</strong>
            <span>You do not have an active library card yet.</span>
            <span style="font-size:13px;">Please visit the library to have your card issued.</span>
        </div>
    @endif
</body>
</html>
