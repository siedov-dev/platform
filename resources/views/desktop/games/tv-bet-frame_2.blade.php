<!DOCTYPE html>
<html>
<head>
    <title>TvBet</title>
    <style>
        * {
            margin: 0;
            padding: 0;
        }
    </style>
</head>
<body>
@if(auth()->check())

    <script src="https://{{ config('game_providers_keys.tvBetIframe') }}/assets/frame.js"></script>
    <script>
        (() => {
            let chatBtn = document.getElementById('supportTrigger');
            if (chatBtn) chatBtn.style.display = 'none';

            let ti = setInterval(() => {
                if (window.TvbetFrame === undefined) return;
                clearInterval(ti);
                new TvbetFrame({
                    'lng': '{{ app()->getLocale() === 'ru' ? 'RU' : 'EN' }}',
                    'clientId': '{{ config('game_providers_keys.tvBetClientId') }}',
                    'tokenAuth': '{!!  \App\GamesProviders\TVBet\LiveRepository::generate(\Auth::user()->id) !!}',
                    'server': 'https://{{ config('game_providers_keys.tvBetIframe') }}',
                    'floatTop': '#fTop',
                    'containerId': 'tvbet-game',
                    'game_id': {{ $_GET['gameId'] ?? 1 }},
                    'refresh-token': '{{ \Illuminate\Support\Str::random(12) }}'
                });
            }, 100);
        })();

    </script>

@else

    @php
        $refreshToken = \Illuminate\Support\Str::random(12);
    @endphp

    <script src="https://{{ config('game_providers_keys.tvBetIframe') }}/assets/frame.js"></script>
    <script>
        (() => {
            let ti = setInterval(() => {
                if (window.TvbetFrame === undefined) return;
                clearInterval(ti);
                new TvbetFrame({
                    'lng': '{{ app()->getLocale() === 'ru' ? 'RU' : 'EN' }}',
                    'clientId': '{{ config('game_providers_keys.tvBetClientId') }}',
                    'tokenAuth': 'test',
                    'server': 'https://{{ config('game_providers_keys.tvBetIframe') }}',
                    'floatTop': '#fTop',
                    'containerId': 'tvbet-game',
                    'game_id': {{ $_GET['gameId'] ?? 1 }},
                    'refresh-token': '{{ $refreshToken }}'
                });
            }, 100);
        })();
    </script>

@endif
</body>
</html>




