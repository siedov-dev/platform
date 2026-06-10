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
    .tournament__games-list {
        display: grid;
        grid-template-columns: repeat(auto-fill,minmax(5rem,1fr));
        grid-gap: 1rem;
    }
    .text-gold{
        color: #ffb500;
    }
    .tournament__cond-text strong{
        font-weight: 500;
        color: #e4b556;
        white-space: nowrap;
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
        <div class="tournament__cond-title mt-2 text-gold">Попрощайтесь з літом зі смаком!</div>

        <div class="tournament__cond-text"><span class="hot">Mystery Fall</span> - це нова акція від Wazdan, під час якої ви зможете виграти призи з великого призового фонду в розмірі
            <strong>3 000 000 євро</strong>. Грайте у свої улюблені ігри від Wazdan та дозвольте <span class="hot">Mystery Drop™</span> виявити свою чарівну силу.</div>

        <div class="tournament__cond-title">Період акції:</div>
        <div class="tournament__cond-text">З <strong>15 вересня по 15 жовтня 2023 року</strong></div>

        <div class="tournament__cond-title">Умови та положення:</div>
        <div class="flex ai-center">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>Акція Mystery Drop ("Акція") доступна лише в режимі гри на реальні гроші.</li>
                    <li>Учасники беруть участь в акції, розміщуючи кваліфіковану ставку в учасницьких іграх протягом періоду акції. <strong>Додаткових витрат немає</strong>.</li>
                    <li>Гравці можуть вигравати більше одного випадкового призу під час акції.</li>
                    <li>Будь-яка кваліфікована ставка в учасницьких іграх протягом періоду акції може запускати приз із призового фонду.</li>
                    <li>Одна кваліфікована ставка може виграти лише один приз. Призи будуть виплачуватися випадковим чином протягом періоду акції.</li>
                    <li><strong>Мінімальний розмір ставки не потрібен</strong>. Усі ставки мають право на участь в акції.</li>
                    <li>Кількість доступних призів оновлюється миттєво.</li>
                </ul>
            </div>
        </div>
        <div class="flex jc-center">
            <img src="/img/tournaments/mystery-fall/chestnut1.png" alt="chestnut" style="width: 250px; margin-top: -10px;">
        </div>

        <div class="flex ai-fe mt-1">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>Всі виграні призи миттєво нараховуються на рахунки гравців у казино після отримання сповіщення про виграш.</li>
                    <li>Призи виплачуватимуться відповідно до вкладки призового фонду.</li>
                    <li>Максимальна загальна вартість грошових призів становить <strong>500 000 євро</strong>, розділених на <strong>5000</strong> Секретних Коробок.</li>
                    <li>Грошові призи <strong>не мають ЖОДНИХ вимог до відіграшу</strong>.</li>
                    <li>Призи, виплачені під час використання бонусних коштів, будуть виплачуватися у вигляді бонусних коштів.</li>
                    <li>Грошові призи будуть виплачуватися у валюті, в якій була розміщена виграшна ставка, з урахуванням конвертації в євро на момент розміщення виграшної ставки.</li>
                </ul>
            </div>
        </div>
        <div class="flex jc-center">
            <img src="/img/tournaments/mystery-fall/nut1.png" alt="nut" style="width: 150px; margin-right: 1rem">
        </div>

        <div class="flex ai-fe">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>Право на виграш призів мають лише перевірені гравці казино.</li>
                    <li>Казино залишає за собою право не виплачувати грошовий приз, якщо виграш виник внаслідок очевидної помилки, непорозуміння або технічної помилки (включаючи невірні виплати в грі), незалежно від того, чи була вона спричинена машинною, чи людською помилкою під час участі в одній з учасницьких ігор. Казино також залишає за собою право не виплачувати грошовий приз, якщо, на думку казино, виграш виник внаслідок шахрайства або співробітництва з іншими гравцями.</li>
                    <li>Казино залишає за собою право в будь-який момент з будь-якої причини змінити, анулювати, призупинити або завершити цю акцію, а також змінити умови цієї пропозиції в будь-який момент. Попередні умови і пропозиції будуть діяти для гравців, які їх заявлять до моменту зміни пропозиції.</li>
                </ul>
            </div>
        </div>
        <div class="flex jc-center">
            <img src="/img/tournaments/mystery-fall/leaf1.png" alt="leaf" style="width: 300px; margin-right: 1rem">
        </div>

        <div class="tournament__cond-title">Приймають участь ігри:</div>
        <div class="tournament__cond-text"><span style="color: red; font-weight: bold">Усі ігри від Wazdan</span></div>

        <div class="tournament__cond-title">Призовий фонд:</div>
        <div class="tournament__cond-text">Максимальна загальна вартість грошових призів становить <strong>500 000 євро</strong>. Всього <strong>5000 Секретних Коробок</strong>. Кожна Секретна Коробка може дарувати призи із наступного списку:</div>

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
                @include('phone.games.single-game', ['game' => $game])
            @endforeach
        </div>
    </div>

    @include('phone.footer')

</div>
</div>
