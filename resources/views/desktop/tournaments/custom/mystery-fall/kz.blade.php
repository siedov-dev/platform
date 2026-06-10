@php
    /** @var \App\Models\Tournaments\Custom\CustomTournament $tournament */
@endphp

<style>
    .tournament__list ul {
        margin: 0.5rem 0 2rem 1.5rem;
    }

    .tournament__list li {
        font-size: 1rem;
        color: #7a79b0;
        margin-top: 0.5rem;
    }

    .tournament__list ul li::before {
        width: 0.5rem;
        height: 0.5rem;
        background-color: #9d8b53;
    }

    ol.tournament__list {
        margin: 0.5rem 0 2rem 1.5rem;
    }

    ol.tournament__list li {
        font-size: 0.875rem;
        /*list-style: none;*/
        color: #fff;
    }

    ol.tournament__list li::before {
        width: 0.5rem;
        height: 0.5rem;
        background-color: #9d8b53;
    }

    .tournament__table {
        border-spacing: 0;
        margin-left: auto;
        margin-right: auto;
    }

    .tournament__table td {
        padding: 0.5rem 1.5rem;
        color: #ffffff;
        border-bottom: 1px solid #ffffff1a;
    }

    .tournament__table th {
        color: #fff;
        text-align: left;
        border-bottom: 2px solid #fff;
        padding: 0.5rem 1rem;
    }

    body.light .tournament__table th {
        color: #333;
        border-color: #333;
    }

    .tournament__image {
        width: 20%;
        height: auto;
    }
    .tournament__cond-text strong{
        font-weight: 500;
        color: red;
    }
    .text-gold{
        color: #ffb500;
    }
    .tournament__cond-text strong{
        font-weight: 500;
        color: #e4b556;
    }
    .hot{
        color: red;
        font-weight: bold;
        white-space: nowrap;
    }
</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" style="width: 100%;">
    </div>

    <div class="article__text">
        <div class="tournament__cond-title mt-2 text-gold">Кезекке салу күндерін тартып отырыңыз!</div>

        <div class="tournament__cond-text"><span class="hot">Mystery Fall</span> - бұл Wazdan компаниясының жаңа промоциясы, қайырымды
            <strong>3 000 000 евро</strong> сыйлау үшін. Сіздің ұнайтын Wazdan ойындарыңызды ойнаңыз және <span class="hot">Mystery Drop™</span> оларды қалаған қалада жұмсап жатады.</div>

        <div class="tournament__cond-title">Кампания мерзімі:</div>
        <div class="tournament__cond-text"><strong>2023 жылдың 15 қыркүйек - 15 қазан жасалады</strong></div>

        <div class="tournament__cond-title">Ережелер және шарттар:</div>
        <div class="flex ai-center">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>Мystery Drop кампаниясы ("Кампания") тек қолдану үшін тіркелгі режимінде қолжетімді.</li>
                    <li>Қатысушылар кампания кезеңінде қатысу үшін квалификациялы бет жасайды. <strong>Қосымша төлем жоқ</strong>.</li>
                    <li>Ойыншылар кампания кезеңінде бірнеше случайлық жеңілдікке ие бола алады.</li>
                    <li>Кампания кезеңінде қатысушылардың участвующие ойындағы квалификациялы беті жеңілдікті орындау алатын беттерді алуы мүмкін.</li>
                    <li>Квалификациялы бет тек бір жеңілдік ала алады. Жеңілдіктер кампания кезеңінде случайлы түрде беріледі.</li>
                    <li>Минимальды бет өлшемі <strong>талап етілмейді</strong>. Барлық беттер кампанияға қатысу үшін қойылады.</li>
                    <li>Қолжетімді жеңілдіктер саны жедел жаңартылады.</li>
                </ul>
            </div>
            <img src="/img/tournaments/mystery-fall/chestnut1.png" alt="chestnut" style="width: 300px; margin-top: -80px;">
        </div>

        <div class="flex ai-fe mt-1">
            <img src="/img/tournaments/mystery-fall/nut1.png" alt="nut" style="width: 200px; margin-right: 1rem">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>Жеңілдіктерге ие болған барлық жеңілдіктер ойыншылардың казино тіркелгілеріне жүргізілген жеңілдік хабарландырылған кезде жұмсалады.</li>
                    <li>Жеңілдіктер призов фонды кестесі бойынша төленеді.</li>
                    <li>Нақты денеженің максималды жұмыс жеткізу құны <strong>500 000 евро</strong>, <strong>5000</strong> Mystery Boxes-ға бөлінеді.</li>
                    <li>Денеже жеңілдіктерінің <strong>ойнау шарттары жоқ</strong>.</li>
                    <li>Бонусты қолдану кезінде берілетін жеңілдіктер бонусты қолдану кезінде төленеді.</li>
                    <li>Денеженің жеңілдіктері жеңілдікті алу уақытындағы денеже басу салу моментінде де алынатын беттің қойылған валютасында төленеді, оның құны евродан алынған беттің қойылған уақытында конвертациялау арқылы.</li>
                </ul>
            </div>
        </div>

        <div class="flex ai-fe">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>Жеңілдіктерге ие болу үшін тек тексерілген казино ойыншылары қатысуға құқылы.</li>
                    <li>Казино, жеңілдіктерін ала тұрған кезде кездесу немесе техникалық сбой (ойындағы дұрыс төлеулерді көрсету үшін барысында) жол ашу, алдағы ойыншылармен шақыру немесе басқа қатынасу жасау үшін келісімге келісу жолында жеңілдік бермеу құқығын сақтайды.</li>
                    <li>Казино, келісімді кезең немесе пропозицияны негізгі және талаптарын белгілеу, жою, тоқтату немесе аяқтау үшін кезде барлық себептер бойынша жоқ етеді және офертаның шарттарын кез келген уақытта өзгерту құқығын сақтайды. Офертаның басты шарттары және пропозициялары оферта өзгерту моментіне дейін қамтамасыз етіледі.</li>
                </ul>
            </div>
            <img src="/img/tournaments/mystery-fall/leaf1.png" alt="leaf" style="width: 300px; margin-right: 1rem">
        </div>

        <div class="tournament__cond-title">Қатысып алушы ойындар:</div>
        <div class="tournament__cond-text"><span style="color: red; font-weight: bold">Барлық Wazdan ойындары</span></div>

        <div class="tournament__cond-title">Призовой фонд:</div>
        <div class="tournament__cond-text">Денежелік жеңілдіктердің максималды құны <strong>500 000 евро</strong>. <strong>5000 Mystery Boxes</strong>-тың барлығы. Әр Mystery Box қосымша жеңілдіктер тізімінен жүргізе алатын болады:</div>
        <div class="flex ai-center">
            <div class="tournament__cond-text ml-3">
                <ul class="tournament__list">
                    <li>100 EUR</li>
                    <li>25 EUR</li>
                    <li>20 EUR</li>
                    <li>15 EUR</li>
                    <li>10 EUR</li>
                    <li>5 EUR</li>
                </ul>
            </div>
            <img src="/img/tournaments/mystery-fall/MysteryDrop_box.png" alt="bunny" style="width: 200px; margin-left: 10px;">
        </div>

    <div class="tournament__games">
        <div class="tournament__games-head">
            <div class="tournament__games-title">{{__('tournament.tournament_games')}}:</div>
        <!--<a class="tournament__games-button" href="{{route('games', ['category' => 'tournament', 't_id' => $tournament->getId()])}}">{{__('tournament.all_games')}} ({{$tournament->getAllowedGames()->count()}})</a>-->
        </div>
        <div class="tournament__games-list">
            @foreach($tournament->getAllowedGames() as $game)
                @include('desktop.games.single-game', ['game' => $game])
            @endforeach
        </div>
    </div>

    @include('desktop.footer')

</div>
</div>
