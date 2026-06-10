@php
    /** @var \App\Models\Tournaments\Custom\CustomTournament $tournament */
@endphp

<style>
    .tournament__list ul {
        margin: 0.5rem 0 2rem 1.5rem;
    }

    .tournament__list ul li {
        font-size: 0.875rem;
        color: #7a79b0;
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
        white-space: nowrap;
        color: #e4b556;
    }
</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" style="width: 100%;">
    </div>

    <div class="article__text">
        <div class="tournament__cond-title">China CashFall жұмыстары:</div>
        <div class="tournament__cond-text">20.07.2023 9:00 UTC-ден <strong>24.07.2023 21:00 UTC-ге</strong></div>

        <div class="tournament__cond-title">3 Oaks Network Tournament шарттары:</div>
        <div class="flex">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>Турнирде қатысу арқылы сіз осында көрсетілген Шарттар мен Міндеттерге келісесіз.</li>
                    <li>Алдын ала жағдайда, 3 Oaks Gaming төлемдерді, ойын тартуларын да (Лаки Дроп арқылы) қажетті санауларды қалпына келтіру құқығын қолдану құқығын қамтамасыз ету құқығына ие.</li>
                    <li>Тек <strong>жауапкершілік ақшаларының</strong> раундтары Лаки Дроп кампаниясына қатысуға мүмкіндік бар.</li>
                    <li><strong>Спин істеу бағасынан басқа</strong>, турнирге ену үшін қосымша құны жоқ.</li>
                    <li>Әр игрокка топтық турнир ID-сі беріледі.</li>
                    <li>Турнирдің ережелері кез келген уақытта өзгертіле алады.</li>
                    <li>Қатерлер барлығын бас тартады.</li>
                </ul>
            </div>
            <img src="/img/tournaments/china-cash-fall/chinese2.webp" alt="palm" style="width: 320px; height: 380px; margin-bottom: 0px; margin-top: -100px;">
        </div>


        <div class="tournament__cond-title">Квалификация:</div>
        <div class="flex">
            <div style="margin-top: 0px;">
                <img src="/img/tournaments/china-cash-fall/bamboo.webp" alt="bamboo" style="margin-left: 1rem; width: 250px; margin-top: 20px">
            </div>
            <div class="tournament__cond-text"> Тапсырысты ұтқару жөніндегі марапаттарды қалайтындау үшін ойыншының келісімдерінің қатынастарын орындауы тиіс:
            <ol class="tournament__list">
                <li><span style="color: red; font-weight: bold">1.00 EUR-дан кем</span> <strong>Міндетті қойылым</strong></li>
                <span style="color: white; text-transform: uppercase; font-weight: bold">және</span>
                <li><span style="color: red; font-weight: bold">150-ден кем</span> <strong>спиндер саны</strong></li>
            </ol>
            Тапсырыстың квалификациялық кезеңінен кейін бір ойыншының жинап алған барлық балдары сақталады және есептеледі. Квалификациясы болмайтын ойыншыларды да Лидердік тізімде көрсетеді, бірақ квалификацияға дейінгірені зымыратты үлгі сыры сұрылып отырады.
        </div>
    </div>

    <div class="tournament__cond-title">Табыс механикасы:</div>
    <div class="tournament__cond-text">
        <div class="tournament__list">
            <span style="color: red; text-transform: uppercase; font-weight: bold"> ЖЕҢІЛДІК ДАУЫС:</span> ойыншы <strong>1 EUR-дан</strong> көптеген көбейісін еткенде және <strong>120 EUR табыс жасағанда,</strong> ол <strong>120 балл алады</strong>.
        </div>
        <div class="tournament__list">
            <span style="color: red; text-transform: uppercase; font-weight: bold"> КӨБЕЙТПЕЙТІНІ МЕЖЕНДІК:</span> ойыншы <strong>2 EUR-дан</strong> көптеген көбейісін еткенде және <strong>120 EUR табыс жасағанда,</strong> ол 120 (табыс)÷2(ставка)= 60x көбейткіш = <strong>60 балл</strong> алады.
        </div>
    </div>

    <div class="tournament__cond-title">Құнылыс Пулы:</div>

    <ul class="tournament__list">
        <div class="tournament__cond-text">Турнирдің жалпы құнылыс пулы - прогрессивтік бастап <span style="color: red; font-weight: bold">40 000 EUR</span> (немесе ойыншының валютасының эквиваленті).</div>
        <div class="tournament__cond-text">Прогрессивтік құнылыс пулы турнир кезінде қатысушылардың ставкаларының пайызымен көтеріледі.</div>
    </ul>


    <div class="flex ai-center mt-2">

        <table class="tournament__table"
               style='box-shadow: #00bcff 0 0 7px; z-index: 2; background-color: #0B0D31C0; margin-top: 70px; width: 60%'>
            <thead>
            <tr>
                <th>Орны</th>
                <th>Приз</th>
                <th>Сомасы</th>
            </tr>
                </thead>
                <tbody>
                <tr><td>1</td><td>10%</td><td>10%</td></tr>
                <tr><td>2</td><td>8%</td><td>8%</td></tr>
                <tr><td>3</td><td>6%</td><td>6%</td></tr>
                <tr><td>4</td><td>5%</td><td>5%</td></tr>
                <tr><td>5</td><td>4%</td><td>4%</td></tr>
                <tr><td>6</td><td>2.5%</td><td>2.5%</td></tr>
                <tr><td>7</td><td>1.6%</td><td>1.6%</td></tr>
                <tr><td>8</td><td>0.9%</td><td>0.9%</td></tr>
                <tr><td>9</td><td>0.8%</td><td>0.8%</td></tr>
                <tr><td>10-20</td><td>0.7%</td><td>7.7%</td></tr>
                <tr><td>21-30</td><td>0.6%</td><td>6%</td></tr>
                <tr><td>31-40</td><td>0.5%</td><td>5%</td></tr>
                <tr><td>41-50</td><td>0.4%</td><td>4%</td></tr>
                <tr><td>51-100</td><td>0.3%</td><td>15%</td></tr>
                <tr><td>101-150</td><td>0.2%</td><td>10%</td></tr>
                <tr><td>151-200</td><td>0.15%</td><td>7.5%</td></tr>
                <tr><td>201-250</td><td>0.12%</td><td>6%</td></tr>
                <tr>
                    <td colspan="2"></td>
                    <td><span style="color: red; text-transform: uppercase; font-weight: bold">100%</span></td>
                </tr>
                </tbody>
            </table>

        </div>
        <div class="flex jc-center">
            <img src="/img/tournaments/china-cash-fall/chgate.webp" alt="gates" style="width: 100%; margin-top: -880px;">
        </div>
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
