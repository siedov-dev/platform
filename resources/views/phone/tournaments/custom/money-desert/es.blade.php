@php
    /** @var \App\Models\Tournaments\Custom\CustomTournament $tournament */
@endphp

<style>
    .tournament__table{
        background-color: #0B0D31C0; 
        width: 100%;
        color: #fff
    }
    .tournament__table td{
        padding: 15px;
        vertical-align: text-top;
    }
    .margintop_30{
        margin-top: 30px;
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
        <div class="tournament__cond-title mt-2">3 Oaks Network Tournament <span style="color: rgb(255, 157, 0)">Money Desert</span> – Prize pool <span style="color: red">50,000</span> Euro</div>
        <div class="tournament__cond-text">
            Play Olaf Viking, Crystal Scarabs, Grab more Gold!, 3 Coins: Egypt, Egypt Fire, Sun of Egypt 2, Coin Volcano, Sun of Egypt 3, More Magic Apple, Black Wolf 2, Green Chilli 2, Aztec Fire, Green Chilli, Hit the Gold!, and win prizes!
        </div>
        <div class="tournament__cond-title">Campaign period:</div>
        <div class="tournament__cond-text">
            <strong>07.12.2023 09:00 UTC</strong> to <strong>13.12.2023 23:59 UTC</strong>
        </div>
        

        <div class="tournament__cond-title">Campaign details</div>
        <div class="flex ">
            <div class="tournament__cond-text">
                
                <table class="tournament__table mob_table" style="color: #fff;">
                    <tbody>
                        <tr>
                            <td>Prize Pool</td>
                            <td>EUR 50,000</td>
                        </tr>
                        <tr>
                            <td>Qualification</td>
                            <td>
                                To be qualified and have the right to claim the prize, the player must fulfill the following conditions:<br>
                                1. The <strong>Minimum Bet</strong> is <strong style="color: red">EUR 0.2</strong>,<br>
                                2. The Minimum <strong>Number of Spins</strong> is <strong style="color: red">150</strong><br>
                                All points collected during the qualification stage are saved and counted for a player after passing
                                the qualification. Non-qualified players are also shown on the Leaderboard, but the line for the NonQualified Player is striped in grey until they are qualified.
                            </td>
                        </tr>
                        <tr>
                            <td>Tournament Scoring</td>
                            <td>
                                <strong style="color: red">WIN RACE:</strong> if the player placed a <strong>EUR 1</strong> total bet and won <strong>EUR 120</strong>, the player will get <strong>120 Points</strong>.<br>
                                <strong style="color: red">MULTIPLIER RACE:</strong> if the player placed a <strong>EUR 2</strong> total bet and won <strong>EUR 120</strong>, the player will get
                                120(win)÷2(bet)= a 60x multiplier = <strong>60 points</strong>.
                            </td>
                        </tr>
                        <tr>
                            <td>In-Game Leaderboard</td>
                            <td>
                                <p>During the Tournament, the Leaderboard is updated in real-time with each player's spin. Without a
                                    player’s activity, player stats are defaulted to update on a 10 seconds sync between the game and
                                    the promo server.
                                </p>
                                <p>
                                    Winning Points are shown and sorted on the Tournament Leaderboard - the best players’ results are
                                    always on TOP.
                                </p>
                                <p>
                                    When forming the Final Leaderboard, we reserve a 5-minute delay to prevent all possible data loss
                                    scenarios; when making final calculations, the Leaderboard will appear striped until complete.
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
            </div>
        </div>
        <div class="flex  margintop_30">
            <img src="/img/tournaments/money-desert/600x800_money_desert.jpg" alt="tree"style="width: 100%;">
            <div class="tournament__cond-text">
                <div class="tournament__cond-title" style="margin: 0;">Prize pool distribution</div>
                <ul class="tournament__list pad_left_20">
                    <li>Prizes are paid automatically to winners if the auto-payout feature is enabled. The winner gets a Win Pop-Up after the
                        Tournament is finished and the final results are displayed on the Leaderboard.
                    </li>
                    <li>
                        If the auto-payout feature is disabled, players get a Win Pop-Up stating that the prize will be manually credited to their accounts
                        by the Operator in 72 hours
                    </li>
                    <li>
                        If the auto-payout feature is unavailable due to any technical issues, Operators reward players manually, based on the Winners
                        Report provided by 3 Oaks.
                    </li>
                    <li>
                        The total prize pool of the Tournament is EUR 50,000 (or the player's currency equivalent)
                    </li>
                    <li>
                        Prize distribution table in EUR:
                    </li>
                </ul>
                <table class="tournament__table margintop_30 mob_table">
                    <thead>
                        <tr>
                            <td>From place</td>
                            <td>To place</td>
                            <td>Prize</td>
                            <td>Sum</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>1</td>
                            <td><strong>€ 5,000</strong></td>
                            <td>€ 5,000</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>2</td>
                            <td><strong>€ 3,500</strong></td>
                            <td>€ 3,500</td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>3</td>
                            <td><strong>€ 2,500</strong></td>
                            <td>€ 2,500</td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td>5</td>
                            <td><strong>€ 1,500</strong></td>
                            <td>€ 3,000</td>
                        </tr>
                        <tr>
                            <td>6</td>
                            <td>10</td>
                            <td><strong>€ 750</strong></td>
                            <td>€ 3,750</td>
                        </tr>
                        <tr>
                            <td>11</td>
                            <td>20</td>
                            <td><strong>€ 550</strong></td>
                            <td>€ 5,000</td>
                        </tr>
                        <tr>
                            <td>21</td>
                            <td>50</td>
                            <td><strong>€ 375</strong></td>
                            <td>€ 11,250</td>
                        </tr>
                        <tr>
                            <td>51</td>
                            <td>100</td>
                            <td><strong>€ 175</strong></td>
                            <td>€ 8,750</td>
                        </tr>
                        <tr>
                            <td>101</td>
                            <td>150</td>
                            <td><strong>€ 100</strong></td>
                            <td>€ 5,000</td>
                        </tr>
                        <tr>
                            <td>151</td>
                            <td>200</td>
                            <td><strong>€ 45</strong></td>
                            <td>€ 2,250</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
       
        <div class="tournament__cond-title">Terms and conditions:</div>
        
        <div class="tournament__cond-text">
            <p>
                1. By participating in the tournament, you agree to the Terms and Conditions outlined here and the ones provided by the
                operator.<br>
                2. In cases of emergency, 3 Oaks Gaming holds the right to cancel the Money Desert promo at any time, including payments for
                winnings, if deemed necessary.<br>
                3. Only qualified players can claim the prize (please check the Campaign Details Section).<br>
                4. Only real money rounds can participate in the tournament.<br>
                5. Other than the price to make a spin, there is no additional cost to enter the tournament.<br>
                6. Each player will get a personal tournament ID for the leaderboard.<br>
                7. If two or more players are tied, those players who get the score first will break the tie.<br>
                8. Tournament rules can be changed anytime.<br>
                9. Malfunction voids all payouts.
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
