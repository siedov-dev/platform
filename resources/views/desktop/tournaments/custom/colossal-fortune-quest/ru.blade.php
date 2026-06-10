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
                Все дело в победах! Yggdrasil приглашает вас присоединиться к веселью и азарту сетевого турнира «Colossal Fortune Quest».
            </p>
            <p>
                <span class="red-text">Разыгрывается общий денежный призовой фонд в размере 40 000 евро!</span> Играйте в любую из участвующих игр, чтобы получить шанс стать одним из победителей случайного розыгрыша призов!
            </p>
            <p class="mb-2">
                Турнир проходит с <span class="red-text">с 10 по 17 октября 2022 г.</span>
                Вернитесь к веселью!
            </p>
            <img src="/img/tournaments/colossal-fortune-quest/pyramid.png" alt="ship" class="tournament__image" style="position: absolute; bottom: 0; right: 0; width: 400px; filter: drop-shadow(1px 1px 2px black);">
        </div>

        <div class="flex ai-center" style="position: relative;">
            <div class="tournament__cond-text">
                <img src="/img/tournaments/colossal-fortune-quest/compass_01.png" alt="compass" class="tournament__image"
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
                <img src="/img/tournaments/colossal-fortune-quest/totem.png" alt="compass" class="tournament__image"
                     style="margin-left: 2rem; position: absolute; bottom: 90px; right: 70px; filter: invert(1) opacity(0.3); width: 150px;">
            </div>
        </div>

        <div class="flex jc-center mt-1 mb-1">
            <img src="/img/tournaments/colossal-fortune-quest/dyno.png" alt="compass" class="tournament__image">
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
                    <tr><td>1</td><td>7 000 €</td><td>420 000 ₽</td><td>560 000 ₹</td><td>280 000 ₴</td><td>3 248 000 ₸</td></tr>
                    <tr><td>2-3</td><td>3 000 €</td><td>180 000 ₽</td><td>240 000 ₹</td><td>120 000 ₴</td><td>1 392 000 ₸</td></tr>
                    <tr><td>4-8</td><td>1 000 €</td><td>60 000 ₽</td><td>80 000 ₹</td><td>40 000 ₴</td><td>464 000 ₸</td></tr>
                    <tr><td>9-18</td><td>550 €</td><td>33 000 ₽</td><td>44 000 ₹</td><td>22 000 ₴</td><td>255 200 ₸</td></tr>
                    <tr><td>19-69</td><td>150 €</td><td>9 000 ₽</td><td>12 000 ₹</td><td>6 000 ₴</td><td>69 600 ₸</td></tr>
                    <tr><td>69-168</td><td>40 €</td><td>2 400 ₽</td><td>3 200 ₹</td><td>1 600 ₴</td><td>18 560 ₸</td></tr>
                    <tr><td>169-668</td><td>10 €</td><td>600 ₽</td><td>800 ₹</td><td>400 ₴</td><td>4 640 ₸</td></tr>
                    <tr style="font-weight: bold;"><td>668 winners</td><td>40 000 €</td><td>2 400 000 ₽</td><td>3 200 000 ₹</td><td>1 600 000 ₴</td><td> 18 800 000 ₸</td></tr>
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
