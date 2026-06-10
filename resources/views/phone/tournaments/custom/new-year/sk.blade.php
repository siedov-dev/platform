@php
    /** @var \App\Models\Tournaments\Custom\CustomTournament $tournament */
@endphp

<style>
    .pad_left_20{
        padding-left: 40px;
    }
    .tournament__table{
        background-color: #0B0D31C0; 
        margin-top: 20px; 
        width: 100%;
        color: #fff
    }
    .tournament__table td{
        padding: 5px;
        text-align: center;
    }
    .margintop_30{
        margin-top: 30px;
    }
    .just_center{
        justify-content: center
    }
    .text-center{
        text-align: center;
    }
    .mob_table{
        display: block;
        overflow-x: auto;
        white-space: nowrap;
    }
    .tour_page .flex{
        display: block
    }
</style>
<div class="width-limiter tour_page">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" style="width: 100%;">
    </div>

    <div class="article__text">
        <div class="tournament__cond-title mt-2">Smartsoft New Year Tournament – <span style="color: red">50,000</span> Euro</div>
        <div class="tournament__cond-text">
            Play JetX, Balloon, FootballX, CricketX, PlinkoX, Multihot5, GeniesBonanza, MultiHotWays, BurningICE40,
            and other games provided by Smartoft complete turnover and multiplier missions, spin the leaderboard
            wheel, and win prizes!
        </div>
        <div class="tournament__cond-title">Campaign period:</div>
        <div class="tournament__cond-text">
            The network tournament series is held from <strong>01.12.2023 – 28.12.2023</strong>. The tournament will be divided
            into two stages; Prize Pool for each stage is <strong>25,000 Euro</strong>. The first stage is held from <strong>01.12.2023</strong> 11:00
            AM (+ 4 UTC) to (including) <strong>14.12.2023</strong> 11:00 AM (+ 4 UTC) and the second stage is held from
            <strong>15.12.2023</strong> 11:00 AM (+ 4 UTC) to (including) <strong>28.12.2023</strong> 11:00 AM (+ 4 UTC).
        </div>
        

        <div class="tournament__cond-title">How to participate?</div>
        <div class="flex ">
            <div class="tournament__cond-text">
                <p>
                    At the start of the promo, players have to check the list of participating games and accomplish running
                    missions. Prizes can be won by finishing among the top 500 players on the leaderboard.
                </p>
                <p>
                    Leaderboard is working on a points system. For receiving points player has to spin a leaderboard wheel.
                    The Leaderboard wheel contains 6 different points: 1,2,10,25,50,100
                </p>
                <p>
                    To get the right to spin the wheel the player must complete one of 2 different types of missions.
                </p>
                <p>
                    The first mission is called the “Turnover mission”. The player will have 10 different phases to pass each
                    day and after accomplishing each of them he/she will receive 1 spin on a leaderboard wheel. Turnover
                    mission requirements:
                </p>
                <ul class="tournament__list pad_left_20">
                    <li>The first phase requires the player to place bets totaling 0.10 Euro;</li>
                    <li>The second phase requires the player to place bets totaling 5.1 Euro</li>
                    <li>
                        The third phase requires the player to place bets totaling 15.1 Euro, and so on for subsequent
                        stages.
                    </li>
                </ul>
                <table class="tournament__table mob_table" style="">
                    <thead>
                        <tr>
                            <td>№1</td>
                            <td>№2</td>
                            <td>№3</td>
                            <td>№4</td>
                            <td>№5</td>
                            <td>№6</td>
                            <td>№7</td>
                            <td>№8</td>
                            <td>№9</td>
                            <td>№10</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>0.10 EURO</td>
                            <td>5 EURO</td>
                            <td>10 EURO</td>
                            <td>25 EURO</td>
                            <td>50 EURO</td>
                            <td>100 EURO</td>
                            <td>250 EURO</td>
                            <td>500 EURO</td>
                            <td>1000 EURO</td>
                            <td>1500 EURO</td>
                        </tr>
                    </tbody>
                </table>
                
            </div>
            <img src="/img/tournaments/new-year/tree.png" alt="tree" style="width: 100%;">
        </div>
        <img src="/img/tournaments/new-year/tile.jpg" alt="tree" class="margintop_30"  style="width: 100%;">
        <div class="flex  margintop_30">
            <div class="tournament__cond-text">
                <p>
                    The second mission is Called the “Multiplier Mission”. Each time a player hits multiplier X400 (or Higher),
                    he/she will get 1 spin on a points wheel. If a player hits 850X he/she will get 2 spins on a wheel.
                </p>
                <p>
                    During the day player can receive spins from both missions – but the daily limit cap is a total of 10 spins
                    from both or any of each mission. After the limit is reached the missions page will be frozen.
                </p>
                <p>
                    Missions progress will be renewed each day at 00:00 (UTC:00), and players will have a chance to start a
                    new collection of wheel spins and points.
                </p>
                <p>
                    For Crash Games there is an extra rule: bet must be collected at least on multiplier X1.5 or higher or must
                    be lost.
                </p>
                <p>
                    Sometimes it can take up to 5 minutes before wheel progress appears on the front screen of the promo –
                    meanwhile, inside the wheel, results are updated immediately.
                </p>
                <p>
                    After spinning the wheel, a player will receive the leaderboard immediately.
                </p>
                <p>
                    If 2 or more players have the same number of points in the leaderboard, then a better position will be
                    given to a player who has earned the last points later than other competitor players.
                </p>
            </div>
        </div>

        
        <div class="tournament__cond-title">The minimum bet for participation in the tournament is 0.10 Euros.</div>
        
        <div class="flex ai-center margintop_30">
            <div class="tournament__cond-text" style="margin: 0;">
                <table class="tournament__table mob_table"  style="margin: 0; min-width: 300px;">
                    <thead>
                        <tr>
                            <td>Prize Place</td>
                            <td>Prizes</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>3,000 Euro</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>2,500 Euro</td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>2,000 Euro</td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td>1,500 Euro</td>
                        </tr>
                        <tr>
                            <td>5</td>
                            <td>1,000 Euro</td>
                        </tr>
                        <tr>
                            <td>6*10</td>
                            <td>500 Euro</td>
                        </tr>
                        <tr>
                            <td>11*20</td>
                            <td>300 Euro</td>
                        </tr>
                        <tr>
                            <td>21*50</td>
                            <td>150 Euro</td>
                        </tr>
                        <tr>
                            <td>51*100</td>
                            <td>50 Euro</td>
                        </tr>
                        <tr>
                            <td>101*200</td>
                            <td>10 Euro</td>
                        </tr>
                        <tr>
                            <td>201*500</td>
                            <td>5 Euro</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <img src="/img/tournaments/new-year/new_year.jpg" alt="tree"style="width: 100%;">
        </div>

       
        <div class="tournament__cond-title">Terms and conditions:</div>
        
        <div class="tournament__cond-text">
            <p>
                1. Only bets placed using real money on the full website, mobile, or mobile app versions participate
                in the tournament.<br>
                2. Participating games that are eligible for this tournament are JetX, Balloon, FootballX, CricketX,
                PlinkoX, SlicerX, Multihot5, GeniesBonanza, BurningICE BurningICE10, BurningICE40, Jungles,
                Vampires, Samurai, Viking, Joker's4Bonuses, TowerX, MultiHotWays, Magic garden 40, Magic garden
                10, Magic garden 5, ParaDice10, JetX3, Babylon, Elves, Aztec, crazyape, cowboy, moonstone,
                fruitcoctail, Dark, Apollo, SummerCocktail, Evolution, Argo, Dark100, Zombies, WW2, CrazyHuntX.<br>
                3. You can spin the points wheel for 30 seconds after it appears. In the case of inactivity, after 30
                seconds the wheel will automatically spin and the player will be rewarded on a random basis.<br>
                4. Leaderboard points and progress will be displayed inside the participating games lobby.<br>
                5. The player who earns the maximum number of points will win the tournament.<br>
                6. At the end of the promo, during 3 working days winning players will receive prizes.<br>
                7. If a loss of connection or technical issue arises, all affected spins are considered invalid and may
                not be included in the tournament results.<br>
                8. Casino and Smartsoft receive the right to cancel any points earned or withhold prizes if they
                believe that all or part of the outcome comes as the result of fraud or collusion with other players.<br>
                9. Smartsoft reserves the right to change, suspend or cancel this offer at any time.<br>
                10. By taking part in this offer, the customer agrees to these terms and conditions and to the general
                terms and conditions of the Casino.<br>
                11. All decisions made by the Casino management team are final.
            </p>
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
