@php
    /** @var \App\Models\Tournaments\Custom\CustomTournament $tournament */
@endphp

<style>
    .tournament__list ul {
        margin: 0.5rem 0 2rem 1.5rem;
    }
    .tournament__list ul li {
        font-size: 0.875rem;
        color: #000000;
    }
    .tournament__list ul li::before {
        width: 0.5rem;
        height: 0.5rem;
        background-color: #000000;
    }
    .article__text ul li::before{
        background-color: #000000;
    }
    .tournament__table {
        border-spacing: 0;
        width: 100%;
        border: 1px solid black;
        background-color: #00000033;
    }
    .tournament__table td {
        padding: 0.5rem 1rem;
        color: #FFFFFF;
        text-shadow: 0 0 2px black;
        border-bottom: 1px solid black;
        white-space: nowrap;
        font-weight: 600;
    }
    .tournament__table tr:last-child td {
        border-bottom: none;
    }
    .tournament__table th {
        color: #fff;
        text-align: left;
        /*background-color: #3c3b89;*/
        border-bottom: 2px solid black;
        padding: 0.5rem 1rem;
    }
    body.light .tournament__table th {
        color: #333;
        border-color: #333;
    }
    .tournament__image {
        width: 40%;
        height: auto;
    }
    .tournament__list li {
        margin: 1rem 0;
        font-size: 0.875rem;
    }
    .article__text p{
        color: #FFFFFF;
        font-size: 1.2rem;
        text-shadow: 1px 1px 2px black;
        text-indent: 1rem;
    }
    .red-text{
        color: red;
        font-weight: bold;
    }
    .tournament__cond-text{
        background-image: url("/img/tournaments/fortune-quest/paper_03.png");
        background-repeat: no-repeat;
        background-position: center;
        background-size: 100% 100%;
        padding: 4rem 6rem;
        color: #000000;
    }

    .article__text ul{
        color: black;
    }
</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" class="tournament__banner-image" style="height: auto;">
    </div>


    <article class="article__text">

        <div style="position: relative; padding-right: 360px;">
            <p class="mt-2">
                Уыңды таңда! Yggdrasil сізді «Spooky Fortune Quest» желілік турнирінің қызығы мен толқуына қосылуға шақырады.
            </p>
            <p>
                <span class="red-text">Жалпы 50 000 еуро көлеміндегі ақшалай жүлделер ұтыс алады!</span> Кездейсоқ ұтыс ойынының жеңімпаздарының бірі болу мүмкіндігіне ие болу үшін кез келген қатысушы ойындарды ойнаңыз!
            </p>
            <p class="mb-2">
                <span class="red-text">Турнир 2022 жылдың 28 қазаны мен 6 қарашасы аралығында өтеді.</span>
                Аруақтардың уақыты келсін!
            </p>
            <img src="/img/tournaments/spooky-fortune-quest/pyramids.png" alt="ship" class="tournament__image" style="position: absolute; bottom: 0; right: 0; width: 400px; filter: drop-shadow(1px 1px 2px black);">
        </div>

        <div class="flex ai-center" style="position: relative;">
            <div class="tournament__cond-text">
                <img src="/img/tournaments/spooky-fortune-quest/d.png" alt="compass" class="tournament__image"
                     style="margin-left: 2rem; position: absolute; top: 90px; right: 70px; filter: invert(1) opacity(0.3); width: 150px;">
                <div class="tournament__cond-title" style="color: black;">Ережелер мен шарттар:</div>
                <ul class="tournament__list" style="padding-bottom: 80px;">
                    <li><span class="red-text">Бағалар немесе ұпайлар жоқ. Идея қарапайым - барлық жүлделер кездейсоқ түрде жасалады.</span> Науқанға қатысу үшін ең төменгі ставка талап етілмейді.</li>
                    <li>Науқанға қатысу үшін ойыншылар қалқымалы терезеде «қазір қосылу» түймесін басу арқылы науқанға қатысу ниетін растауы керек. Қатысушы ойыншылар науқан кезеңінде қатысушы ойындарға бәс тігу арқылы науқанға қатысады. Науқанға қатысу үшін қосымша төлем талап етілмейді.</li>
                    <li><span class="red-text">кез келген қатысушы ойындарға</span> қойылған кез келген ставка жүлде қорынан кездейсоқ жұмбақ жүлдені іске қосады.</li>
                    <li>Жүлделердің қалған саны әр 60 секунд сайын жаңартылып отырады.</li>
                    <li>Жүлделер науқанның барлық уақытында беріледі. Үш ең бағалы сыйлықтың кем дегенде біреуі («бас жүлде») науқанның қорытынды кезеңінде беріледі, бұл кезең науқанның жалпы кезеңінің 5%-ына тең болады (мысалы, егер науқан кезеңі 100 сағатты құраса). ). , соңғы 5 сағат ішінде кем дегенде бір бас жүлде беріледі).</li>
                    <li>Әр ставка тек 1 кездейсоқ жүлде ұтып алады.</li>
                    <li>Ойыншылар науқан барысында 1-ден көп кездейсоқ жүлде ұтып ала алады.</li>
                    <li>Егер ұпайлардың барлығы немесе бір бөлігі машина немесе адам қателігінен туындаған кез келген айқын қатенің, қатенің немесе техникалық ақаудың (соның ішінде қате ойын төлемдерінің) нәтижесі болса, Yggdrasil ұпайларды жою немесе жүлдені төлемеу құқығын өзіне қалдырады. қатысатын ойындардың кез келгеніне қатысты. Сонымен қатар, егер біздің ойымызша, нәтижелердің барлығы немесе бір бөлігі алаяқтық немесе басқа ойыншылармен сөз байласу нәтижесі болса, біз нәтижелерді жоққа шығару немесе жүлдені төлемеу құқығын өзімізде қалдырамыз. </li>
                    <li>Науқанға нақты ойын режимінде орналастырылған ставкалар ғана қатысады.</li>
                </ul>
                <img src="/img/tournaments/spooky-fortune-quest/skull.png" alt="compass" class="tournament__image"
                     style="margin-left: 2rem; position: absolute; bottom: 90px; right: 70px; filter: invert(1) opacity(0.3); width: 150px;">
            </div>
        </div>

        <div class="flex jc-center mt-1 mb-1">
            <img src="/img/tournaments/spooky-fortune-quest/wrecked.png" alt="compass" class="tournament__image">
        </div>
        <div class="flex ai-center mt-2" style="background: url('/img/tournaments/fortune-quest/map2.png') no-repeat center; background-size: contain;" >
            <div class="" style="padding-bottom: 10rem; padding-top: 10rem; margin-right: auto; margin-left: auto;">
                <table class="tournament__table mt-2" style="">
                    <thead>
                    <tr>
                        <th>Winners</th>
                        <th>EUR</th>
                        <th>RUB</th>
                        <th>INR</th>
                        <th>UAH</th>
                        <th>KZT</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr><td>1</td><td>10 000 €</td><td>600 000 ₽</td><td>800 000 ₹</td><td>400 000 ₴</td><td>4 700 000 ₸</td></tr>
                    <tr><td>2-3</td><td>5 000 €</td><td>300 000 ₽</td><td>400 000 ₹</td><td>200 000 ₴</td><td>2 350 000 ₸</td></tr>
                    <tr><td>4-8</td><td>1 000 €</td><td>60 000 ₽</td><td>80 000 ₹</td><td>40 000 ₴</td><td>470 000 ₸</td></tr>
                    <tr><td>9-18</td><td>500 €</td><td>30 000 ₽</td><td>40 000 ₹</td><td>20 000 ₴</td><td>235 000 ₸</td></tr>
                    <tr><td>19-69</td><td>100 €</td><td>6 000 ₽</td><td>8 000 ₹</td><td>4 000 ₴</td><td>47 000 ₸</td></tr>
                    <tr><td>69-168</td><td>50 €</td><td>3 000 ₽</td><td>4 000 ₹</td><td>2 000 ₴</td><td>23 500 ₸</td></tr>
                    <tr><td>169-668</td><td>20 €</td><td>1 200 ₽</td><td>1 000 ₹</td><td>800 ₴</td><td>9 400 ₸</td></tr>
                    <tr style="font-weight: bold;"><td>668 winners</td><td>50 000 €</td><td>3 000 000 ₽</td><td>4 000 000 ₹</td><td>2 000 000 ₴</td><td> 23 500 000 ₸</td></tr>
                    </tbody>
                </table>
            </div>
        </div>




    </article>



    <div class="tournament__games">
        <div class="tournament__games-head">
            <div class="tournament__games-title">{{__('tournament.tournament_games')}}:</div>
            <a class="tournament__games-button" href="{{route('games', ['category' => 'tournament', 't_id' => $tournament->getId()])}}">{{__('tournament.all_games')}} ({{$tournament->getAllowedGames()->count()}})</a>
        </div>
        <div class="tournament__games-list">
            @foreach($tournament->getAllowedGames() as $game)
                @include('desktop.games.single-game', ['game' => $game])
            @endforeach
        </div>
    </div>

    @include('desktop.footer')

</div>
