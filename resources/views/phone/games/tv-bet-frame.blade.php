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

    <script src="https://tvbetframe8.com/assets/frame.js"></script>
    <script>
        (() => {
            let chatBtn = document.getElementById('supportTrigger');
            if (chatBtn) chatBtn.style.display = 'none';

            let ti = setInterval(() => {
                if (window.TvbetFrame === undefined) return;
                clearInterval(ti);
                new TvbetFrame({
                    'lng': '{{ app()->getLocale() === 'ru' ? 'RU' : 'EN' }}',
                    'clientId': '1383',
                    'tokenAuth': '{!!  \App\GamesProviders\TVBet\LiveRepository::generate(\Auth::user()->id) !!}',
                    'server': 'https://tvbetframe8.com',
                    'floatTop': '#fTop',
                    'containerId': 'tvbet-game',
                    'game_id': {{ $_GET['gameId'] ?? 1 }}
                });
            }, 100);
        })();

    </script>

@else

    <script src="https://tvbetframe8.com/assets/frame.js"></script>
    <script>
        (() => {
            let ti = setInterval(() => {
                if (window.TvbetFrame === undefined) return;
                clearInterval(ti);
                new TvbetFrame({
                    'lng': '{{ app()->getLocale() === 'ru' ? 'RU' : 'EN' }}',
                    'clientId': '1383',
                    'tokenAuth': 'test',
                    'server': 'https://tvbetframe8.com',
                    'floatTop': '#fTop',
                    'containerId': 'tvbet-game',
                    'game_id': {{ $_GET['gameId'] ?? 1 }}
                });
            }, 100);
        })();
    </script>

@endif
</body>
</html>




