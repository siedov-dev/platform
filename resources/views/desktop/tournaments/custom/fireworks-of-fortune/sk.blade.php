@php
    /** @var \App\Models\Tournaments\Custom\CustomTournament $tournament */
@endphp

<style>
    .orange_bg{
        background: #d8b818!important;
        color: #000;
        font-weight: 600;
    }
    .tnt_table{
        width: 100%;
        color: #000
    }
    .tnt_table td{
        padding: 10px;
        background: #fff;
        text-align: center;
    }
    .tablett{
        width: 100%;
        color: #000;
    }
    .tablett td{
        padding: 5px;
        background: #d8b818;
    }
    .tablett2{
        width: 100%;
    }
    .tablett2 td{
        padding: 5px;
        background: #fff;
        color: #000;
        font-weight: 600;
        width: 50%;
    }
    .pdis_table{
        color: #fff;
        width: 100%;
    }
    .pdis_table td{
        padding: 3px;
        text-align: center;
        background: #fff;
        color: #000;
        font-weight: 600;
    }
    .pdis_table_title{
        padding: 5px;
        background: #d8b818;
        color: #000;
        font-weight: 600;
        margin: 2px;
        margin-top: 30px;
    }
</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" style="width: 100%;">
    </div>

    <div class="article__text">
        <div class="tournament__cond-title mt-2">3 Oaks Network Tournament Series <span style="color: #d8b818">Fireworks of Fortune</span> – Prize Pool <span style="color: red">100,000</span> Euro</div>
        
        
        <div class="flex">
            <div>
                <img src="/img/tournaments/fireworks-of-fortune/344x515_fireworks_of_fortune.jpg" alt="tree" style="height: 464px; margin-right: 30px; margin-top: 30px;">
            </div>
            <div>
                <div class="tournament__cond-title">Fireworks of Fortune run:</div>
                <div class="tournament__cond-text">
                    <span style="color: #d8b818;">28.12.2023 00:00</span> UTC to <span style="color: #d8b818;">02.01.2024 23:00</span> UTC
                </div>
                
                <div class="tournament__cond-title">Grand Total Prize Pool:</div>
                <div class="tournament__cond-text">
                    The Grand Total Prize Pool for this Campaign - <span style="color: red">EUR 100,000</span> (or the player’s currency equivalent) - is combined from several pools according to the following structure:<br>
                        <span style="color: red">EUR 100,000</span> for the period of <span style="color: red">28.12.2023 00:00 UTC</span> to <span style="color: red">02.01.2024 23:00 UTC</span>
                        <ul>
                            <li><span style="color: red">EUR 20,000</span> is awarded for each Regular Tournament for a total of <span style="color: red">EUR 60,000</span> in <span style="color: red">3</span> tournaments</li>
                            <li><span style="color: red">EUR 40,000</span> is awarded via the Series Leaderboard in 1 individual Series</li>
                        </ul>
                </div>
                
                <div class="tournament__cond-title">Tournament Prize Pool:</div>
                <div class="tournament__cond-text">
                    <p><strong style="color: red">EUR 20,000</strong> (or the player’s currency equivalent) is awarded for each Tournament for a Grand Total of <strong style="color: red">EUR 60,000</strong> in 3 tournaments.</p>
                    <p>Please, check the <strong>Prize Pool Distribution</strong> Section for more details.</p>
                </div>
                
                <div class="tournament__cond-title">Series Extra Prize Pool:</div>
                <div class="tournament__cond-text">
                    <p><strong style="color: red">EUR 40,000</strong> is awarded via the Series Leaderboard in 1 individual Series.</p>
                    <p>Please, check the <strong style="color: red">Prize Pool Distribution Section</strong> for more details. </p>
                </div>
            </div>
        </div>

        <div class="tournament__cond-title">Tournament Series Structure:</div>
        <div class="tournament__cond-text">
            <p><span style="color: red">1</span> individual Series with <span style="color: red">3</span> Regular Tournaments each:</p>
            <table class="tnt_table">
                <thead class="">
                    <tr>
                        <td class="orange_bg" style="text-align: center;">Fireworks of Fortune 28.12 - 02.01</td>
                    </tr>
                </thead>
            </table>
            <table class="tnt_table">
                <tbody>
                    <tr>
                        <td class="orange_bg">TNTs</td>
                        <td>TNT1</td>
                        <td>TNT2</td>
                        <td>TNT3</td>
                        <td class="orange_bg">Series</td>
                    </tr>
                    <tr>
                        <td class="orange_bg">Start Time</td>
                        <td>
                            28.12.2023 00:00 UTC
                        </td>
                        <td>
                            30.12.2023 00:00 UTC
                        </td>
                        <td>
                            01.01.2024 00:00 UTC
                        </td>
                        <td class="orange_bg">28.12.2023 00:00 UTC</td>
                    </tr>
                    <tr>
                        <td class="orange_bg">Finish Time</td>
                        <td>29.12.2023 23:00 UTC</td>
                        <td>31.12.2023 23:00 UTC</td>
                        <td>02.01.2024 23:00 UTC</td>
                        <td class="orange_bg">02.01.2024 23:00 UTC</td>
                    </tr>
                    <tr>
                        <td class="orange_bg">Scoring</td>
                        <td>HSRM</td>
                        <td>HSRP</td>
                        <td>HSRM</td>
                        <td class="orange_bg"></td>
                    </tr>
                    <tr>
                        <td class="orange_bg">Prize Pool</td>
                        <td>EUR 20,000 </td>
                        <td>EUR 20,000 </td>
                        <td>EUR 20,000 </td>
                        <td class="orange_bg">EUR 40,000</td>
                    </tr>
                </tbody>
            </table>
        </div>
       

        <div class="tournament__cond-title">Tournament Prize Pool:</div>
        <div class="tournament__cond-text">
            <p><strong style="color: red">EUR 20,000</strong> (or the player’s currency equivalent) is awarded for each Tournament for a Grand Total of <strong style="color: red">EUR 60,000</strong> in 3 tournaments.</p>
            <p>Please, check the <strong>Prize Pool Distribution</strong> Section for more details.</p>
        </div>

        <div class="tournament__cond-title">Tournament Scoring (Winning Mechanic):</div>
        <div class="tournament__cond-text">
            <p>
                <strong style="color: red">HSRP:</strong> players will get points for the highest payout in a single round.<br>
                <strong style="color: red">HSRM:</strong> players will get points for the highest multiplier in a single round
          
            </p>
        </div>
        <div class="tournament__cond-title">Series Extra Scoring (Winning Mechanic):</div>
        <div class="tournament__cond-text">
            <p>Players are getting Series Extra Points during the entire Campaign, based on the following criteria</p>
            <table class="tablett">
                <tr>
                    <td style="width: 50%;">For qualifying in each Regular TNT</td>
                    <td>200 points</td>
                </tr>
            </table>
            <table class="tablett">
                <tr>
                    <td style="width: 50%;">For Every EUR 100 in Bets during the entire Regular TNT</td>
                    <td>1 point</td>
                </tr>
            </table>
            <table class="tablett">
                <tr>
                    <td style="width: 50%;">For Place in the Regular TNT</td>
                    <td>
                        <table class="tablett2">
                            <tr>
                                <td>1st</td>
                                <td>1000 points</td>
                            </tr>
                            <tr>
                                <td>2nd</td>
                                <td>500 points</td>
                            </tr>
                            <tr>
                                <td>3rd</td>
                                <td>250 points</td>
                            </tr>
                            <tr>
                                <td>4th-5th</td>
                                <td>200 points</td>
                            </tr>
                            <tr>
                                <td>6th-10th</td>
                                <td>150 points</td>
                            </tr>
                            <tr>
                                <td>11th-100th</td>
                                <td>50 points</td>
                            </tr>
                            <tr>
                                <td>101st-150th</td>
                                <td>30 points</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <img src="/img/tournaments/fireworks-of-fortune/1200x300_fireworks_of_fortune.jpg" alt="tree" style="width: 100%; margin-top: 30px;">
        </div>

        <div class="tournament__cond-text">
            <div class="flex">
                <div>
                    <div class="tournament__cond-title">Prize pool distribution:</div>
                    <p>
                        1. Prizes are paid automatically to winners if the <strong>auto-payout feature</strong> is enabled. The player gets a Win Pop-Up after the
                        Tournament is finished and the final results are displayed on the Leaderboard.<br>
                        2. If the <strong>auto-payout feature is disabled</strong>, players get a Win Pop-Up stating that the prize will be manually credited to their
                        accounts by the Operator in <strong style="color: red;">72 hours</strong>.<br>
                        3. If the auto-payout feature is unavailable due to any technical issues, Operators reward players manually, based on the Winners
                        Report provided by 3 Oaks.
                    </p>
                </div>
                <img src="/img/tournaments/fireworks-of-fortune/600x700_fireworks_of_fortune.jpg" alt="tree" style="width: 200px; margin-left: 30px; margin-top: 30px;">
            </div>
           <div class="flex">
                <div style="width: 50%;">
                    <div class="pdis_table_title">REGULAR Tournament Prize Pool in EUR</div>
                    <table class="pdis_table">
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
                                <td>€ 2,000</td>    
                                <td>€ 2,000</td>    
                            </tr>   
                            <tr>
                                <td>2</td>    
                                <td>2</td>    
                                <td>€ 1,500</td>    
                                <td>€ 1,500</td>    
                            </tr>   
                            <tr>
                                <td>3</td>    
                                <td>3</td>    
                                <td>€ 1,000</td>    
                                <td>€ 1,000</td>    
                            </tr>   
                            <tr>
                                <td>4</td>    
                                <td>4</td>    
                                <td>€ 750</td>    
                                <td>€ 750</td>    
                            </tr> 
                            <tr>
                                <td>5</td>    
                                <td>5</td>    
                                <td>€ 600</td>    
                                <td>€ 600</td>    
                            </tr>
                            <tr>
                                <td>6</td>    
                                <td>8</td>    
                                <td>€ 400</td>    
                                <td>€ 1,200</td>    
                            </tr>  
                            <tr>
                                <td>9</td>    
                                <td>22</td>    
                                <td>€ 250</td>    
                                <td>€ 3,500</td>    
                            </tr> 
                            <tr>
                                <td>23</td>    
                                <td>50</td>    
                                <td>€ 150</td>    
                                <td>€ 4,200</td>    
                            </tr> 
                            <tr>
                                <td>51</td>    
                                <td>100</td>    
                                <td>€ 75</td>    
                                <td>€ 3,750</td>    
                            </tr> 
                            <tr>
                                <td>101</td>    
                                <td>150</td>    
                                <td>€ 30</td>    
                                <td>€ 1,500</td>    
                            </tr> 
                            <tr>
                                <td> </td>    
                                <td> </td>    
                                <td> </td>    
                                <td>€ 20,000</td>    
                            </tr> 
                        </tbody> 
                    </table>
                </div>
                <div style="width: 50%;">
                    <div class="pdis_table_title">SERIES Leaderboard Prize Pool in EUR</div>
                    <table class="pdis_table">
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
                                <td>€ 4,000</td>    
                                <td>€ 4,000</td>    
                            </tr> 
                            <tr>
                                <td>2</td>    
                                <td>2</td>    
                                <td>€ 3,500</td>    
                                <td>€ 3,500</td>    
                            </tr> 
                            <tr>
                                <td>3</td>    
                                <td>3</td>    
                                <td>€ 3,000</td>    
                                <td>€ 3,000</td>    
                            </tr> 
                            <tr>
                                <td>4</td>    
                                <td>4</td>    
                                <td>€ 2,500</td>    
                                <td>€ 2,500</td>    
                            </tr> 
                            <tr>
                                <td>5</td>    
                                <td>5</td>    
                                <td>€ 2,000</td>    
                                <td>€ 2,000</td>    
                            </tr> 
                            <tr>
                                <td>6</td>    
                                <td>9</td>    
                                <td>€ 1000</td>    
                                <td>€ 4,000</td>    
                            </tr> 
                            <tr>
                                <td>10</td>    
                                <td>17</td>    
                                <td>€ 750</td>    
                                <td>€ 6,000</td>    
                            </tr> 
                            <tr>
                                <td>18</td>    
                                <td>26</td>    
                                <td>€ 400</td>    
                                <td>€ 3,600</td>    
                            </tr> 
                            <tr>
                                <td>27</td>    
                                <td>41</td>    
                                <td>€ 200</td>    
                                <td>€ 3,000</td>    
                            </tr> 
                            <tr>
                                <td>42</td>    
                                <td>66</td>    
                                <td>€ 100</td>    
                                <td>€ 2,500</td>    
                            </tr>
                            <tr>
                                <td>67</td>    
                                <td>91</td>    
                                <td>€ 75</td>    
                                <td>€ 1,875</td>    
                            </tr>
                            <tr>
                                <td>92</td>    
                                <td>143</td>    
                                <td>€ 50</td>    
                                <td>€ 2,600</td>    
                            </tr>
                            <tr>
                                <td>144</td>    
                                <td>200</td>    
                                <td>€ 25</td>    
                                <td>€ 1,425</td>    
                            </tr>
                            <tr>
                                <td></td>    
                                <td></td>    
                                <td></td>    
                                <td>€ 40,000</td>    
                            </tr>
                        </tbody>
                    </table>
                </div>
           </div>
        </div>


       
        <div class="tournament__cond-title">Terms and conditions:</div>
        
        <div class="tournament__cond-text">
            <p>
                1. By participating in the tournament, you agree to the Terms and Conditions outlined here and the ones provided by the
                operator.<br>
                2. In cases of emergency, 3 Oaks Gaming holds the right to cancel the Sparkling Spins promo at any time, including payments
                for winnings, if deemed necessary.<br>
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
