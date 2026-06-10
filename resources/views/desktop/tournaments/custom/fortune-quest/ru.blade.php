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
        font-size: 1rem;
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

        <div style="position: relative; padding-right: 300px;">
            <p class="mt-2">
                Прощай лето! Здравствуй, осень! Yggdrasil приглашает вас присоединиться к веселью и азарту сетевого турнира «Grand Fortune Quest».
            </p>
            <p>
                Общий денежный призовой фонд в размере <span class="red-text">30 000 евро</span> будет разыгран! Сыграйте в любую из участвующих игр, чтобы получить шанс оказаться среди случайных победителей розыгрыша призов!
            </p>
            <p class="mb-2">
                Турнир проходит с <span class="red-text">с 19 по 25 сентября 2022 г.</span>.
                Время перевернуть новый лист!
            </p>
            <img src="/img/tournaments/fortune-quest/ship_01.png" alt="ship" class="tournament__image" style="position: absolute; bottom: 0; right: 0; width: 300px; filter: drop-shadow(1px 1px 2px black);">
        </div>

        <div class="flex ai-center" style="position: relative;">
            <div class="tournament__cond-text">
                <img src="/img/tournaments/fortune-quest/compass_01.png" alt="compass" class="tournament__image"
                     style="margin-left: 2rem; position: absolute; top: 90px; right: 70px; filter: invert(1) opacity(0.3); width: 150px;">
                <div class="tournament__cond-title" style="color: black;">Условия и положения:</div>
                <ul class="tournament__list" style="padding-bottom: 80px;">
                    <li><span class="red-text">Рейтинги и баллы отсутствуют. Идея проста - все призы генерируются случайным образом.</span> Для участия в акции не требуется минимальная ставка.</li>
                    <li>Чтобы принять участие в кампании, игроки должны подтвердить свое желание принять участие в кампании, нажав «присоединиться сейчас» во всплывающем окне. Участвующие игроки примут участие в кампании, сделав ставку в участвующих играх в течение периода кампании. Для участия в кампании дополнительная оплата не требуется.</li>
                    <li>Любая ставка, размещенная в <span class="red-text">любой из участвующих игр</span>, запускает случайный загадочный приз из призового фонда.</li>
                    <li>Оставшееся количество призов обновляется каждые 60 секунд.</li>
                    <li>Призы будут присуждаться в течение всего периода кампании. Минимум один из трех самых ценных призов («главный приз») будет присужден в течение заключительного периода кампании, такой период равен 5% от общего периода кампании (например, если период кампании составляет 100 часов). , в течение последних 5 часов будет присужден как минимум один главный приз).</li>
                    <li>Каждая ставка может принести только 1 случайный приз.</li>
                    <li>Во время кампании игроки могут выиграть более 1 случайного приза.</li>
                    <li>Yggdrasil оставляет за собой право аннулировать очки или не выплачивать приз, если все или часть очков являются результатом какой-либо очевидной ошибки, ошибки или технической неисправности (включая неправильные игровые выплаты), вызванной ошибкой машины или человека. в отношении любой из участвующих игр. Кроме того, мы оставляем за собой право аннулировать результаты или не выплачивать приз, если, по нашему мнению, все или часть результатов являются результатом мошенничества или сговора с другими игроками. </li>
                    <li>В акции участвуют только ставки, сделанные в реальном режиме игры.</li>
                </ul>
                <img src="/img/tournaments/fortune-quest/skul_01.png" alt="compass" class="tournament__image"
                     style="margin-left: 2rem; position: absolute; bottom: 90px; left: 70px; filter: invert(1) opacity(0.3); width: 150px;">
                <img src="/img/tournaments/fortune-quest/trees.png" alt="compass" class="tournament__image"
                     style="margin-left: 2rem; position: absolute; bottom: 90px; right: 70px; filter: invert(1) opacity(0.3); width: 150px;">
            </div>
        </div>

        <div class="flex jc-center mt-1 mb-1">
            <img src="/img/tournaments/fortune-quest/mountains.png" alt="compass" class="tournament__image">
        </div>
        <div class="flex ai-center mt-2" style="background: url('/img/tournaments/fortune-quest/map2.png') no-repeat center; background-size: contain;" >
            <div class="" style="padding-bottom: 3rem; margin-right: auto; margin-left: auto;">
                <table class="tournament__table mt-2" style="">
                    <thead>
                    <tr>
                        <th>Winners</th>
                        <th>EUR</th>
                        <th>RUB</th>
                        <th>INR</th>
                        <th>UAH</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr><td>1</td><td>5 000 €</td><td>335 000 ₽</td><td>400 000 ₹</td><td>160 000 ₴</td></tr>
                    <tr><td>2-3</td><td>2 000 €</td><td>134 000 ₽</td><td>160 000 ₹</td><td>64 000 ₴</td></tr>
                    <tr><td>4-8</td><td>700 €</td><td>46 900 ₽</td><td>56 000 ₹</td><td>22 400 ₴</td></tr>
                    <tr><td>9-18</td><td>300 €</td><td>20 100 ₽</td><td>24 000 ₹</td><td>9 600 ₴</td></tr>
                    <tr><td>19-69</td><td>80 €</td><td>5 360 ₽</td><td>6 400 ₹</td><td>2 560 ₴</td></tr>
                    <tr><td>69-168</td><td>30 €</td><td>2 010 ₽</td><td>2 400 ₹</td><td>960 ₴</td></tr>
                    <tr><td>169-668</td><td>15 €</td><td>1 005 ₽</td><td>1 200 ₹</td><td>480 ₴</td></tr>
                    <tr style="font-weight: bold;"><td>668 winners</td><td>30 000 €</td><td>2 010 000 ₽</td><td>2 400 000 ₹</td><td>960 000 ₴</td></tr>
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
