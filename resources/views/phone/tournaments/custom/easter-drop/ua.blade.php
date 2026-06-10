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

</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" style="width: 100%;">
    </div>

    <div class="article__text">
        <div class="tournament__cond-title mt-2">Візьміть участь у великодньому випуску Wazdan і виграйте частину величезного призового фонду <span style="color: red; font-weight: жирний">€250 000</span>! </div>
        <div class="tournament__cond-title">Період кампанії:</div>
        <div class="tournament__cond-text">З <strong>3 – 16 квітня 2023 г.</strong></div>

        <div class="tournament__cond-title">Положення та умови:</div>
        <div class="flex ai-центр">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>Компанія Mystery Box («Кампанія») доступна лише для гри на реальні гроші.</li>
                    <li> Учасники беруть участь у кампанії, розмістивши кваліфікаційну ставку в іграх, що беруть участь, протягом періоду кампанії. <strong>Додаткових витрат немає</strong>.</li>
                    <li> Під час кампанії гравці можуть виграти більше одного випадкового призу.</li>
                    <li> Будь-яка кваліфікаційна ставка в іграх, що беруть участь, протягом періоду кампанії може призвести до розіграшу призу з призового фонду.</li>
                    <li> Кваліфікаційна ставка може принести лише один приз. Призи будуть присуджені випадково протягом усього періоду кампанії.</li>
                    <li> <strong>Немає вимог щодо мінімального розміру ставки</strong>. Усі ставки підходять для участі у кампанії.</li>
                    <li> Кількість доступних призів оновлюється миттєво.</li>
                </ul>
            </div>
        </div>
        <div class="flex jc-center">
            <img src="/img/tournaments/easter-drop/bunny_1C.png" alt="bunny" style="width: 200px;">
        </div>

        <div class="flex ai-fe">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li> Усі виграні призи будуть миттєво зараховані на рахунки гравців у казино після отримання повідомлення про виграш.</li>
                    <li> Призи будуть сплачені відповідно до вкладки призового фонду.</li>
                    <li> Максимальна загальна сума грошових призів становить <strong>250 000 євро</strong>, поділених на <strong>2500</strong> Mystery Box.</li>
                    <li> Грошові призи <strong>не вимагають відіграшу</strong>.</li>
                    <li> Призи, виплачені під час використання бонусних коштів, будуть виплачені бонусними коштами.</li>
                    <li> Грошові призи будуть виплачені у валюті, в якій було зроблено виграшну ставку, відповідно до конвертації валюти з євро на момент розміщення виграшної ставки.</li>
                </ul>
            </div>
        </div>

        <div class="flex jc-center mt-2">
            <img src="/img/tournaments/easter-drop/MagicEggs_green.png" alt="bunny" style="width: 30%;">
            <img src="/img/tournaments/easter-drop/MagicEggs_blue.png" alt="bunny" style="width: 30%;">
            <img src="/img/tournaments/easter-drop/MagicEggs_dark_blue.png" alt="bunny" style="width: 30%;">
        </div>

        <div class="flex ai-fe">
            {{--            <img src="/img/tournaments/easter-drop/bunny_2B.png" alt="bunny" style="width: 250px;">--}}
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li> Тільки перевірені гравці казино можуть виграти призи.</li>
                    <li> Казино залишає за собою право не виплачувати грошовий приз, якщо виграш є результатом будь-якої очевидної помилки, помилки або технічної несправності (включаючи неправильні ігрові виплати), будь то машинна чи людська помилка щодо будь-якої з учасників гри. Казино також залишає за собою право не сплачувати грошовий приз, якщо, на його думку, виграш є результатом шахрайства або змови з іншими гравцями.</li>
                    <li> Казино залишає за собою право модифікувати, змінювати, припиняти чи припиняти цю акцію у будь-який час з будь-якої причини, а також залишає за собою право змінювати умови цієї пропозиції у будь-який час. Попередні умови та пропозиції будуть дійсними для гравців, які подали заявку, до моменту внесення будь-яких змін до пропозиції.</li>
                </ul>
            </div>
        </div>

        <div class="flex jc-center">
            <img src="/img/tournaments/easter-drop/chick.png" alt="bunny" style="width: 200px;">
        </div>

        <div class="tournament__cond-title">Ігри, що беруть участь:</div>
        <div class="tournament__cond-text"><span style="color: red; font-weight: bold">Всі ігри wazdan</span></div>

        <div class="tournament__cond-title">Призовий фонд:</div>
        <div class="tournament__cond-text">Максимальна загальна сума грошових призів становить <strong>250 000 євро</strong>. Усього є <strong>2500 Mystery Box</strong>. У кожній Mystery Box можна отримати приз із наступного списку:</div>
        <div class="flex ai-center">
            <div class="tournament__cond-text ml-3">
                <ul class="tournament__list">
                    <li>100 EUR</li>
                    <li>75 EUR</li>
                    <li>50 EUR</li>
                    <li>25 EUR</li>
                    <li>10 EUR</li>
                    <li>5 EUR</li>
                </ul>
            </div>
            <img src="/img/tournaments/easter-drop/MysteryDrop_box.png" alt="bunny" style="width: 180px; margin-left: 10px;">
        </div>

    <div class="tournament__games">
        <div class="tournament__games-head">
            <div class="tournament__games-title">{{__('tournament.tournament_games')}}:</div>
        <!--<a class="tournament__games-button" href="{{route('games', ['category' => 'tournament', 't_id' => $tournament->getId()])}}">{{__('tournament.all_games')}} ({{$tournament->getAllowedGames()->count()}})</a>-->
        </div>
        <div class="tournament__games-list">
            @foreach($tournament->getAllowedGames() as $game)
                @include('phone.games.single-game', ['game' => $game])
            @endforeach
        </div>
    </div>

    @include('desktop.footer')

</div>
