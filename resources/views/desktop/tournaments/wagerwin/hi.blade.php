@php
/** @var \App\Models\Tournaments\OnedaygameTournament $tournament */
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
    .tournament__table {
        border-spacing: 0;
        width: 100%;
        border: 1px solid #3c3b89;
    }
    .tournament__table td {
        padding: 0.5rem 1rem;
        color: #FFF;
        border-bottom: 1px solid #3c3b89;

    }
    .tournament__table tr:last-child td {
        border-bottom: none;
    }
    .tournament__table th {
        color: #fff;
        text-align: left;
        background-color: #3c3b89;
        border-bottom: 2px solid #3c3b89;
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
    .yellow-text{
        color: #fcc339;
        font-weight: 500;
    }
    .article__text p{
        color: #FFFFFF;
        font-size: 1.2rem;
        text-shadow: 1px 1px 2px black;
        text-indent: 1rem;
        font-weight: 600;
    }
</style>
<div class="width-limiter">



    <div class="article__text">
        <div class="flex ai-center">
            <div class="tournament_cond-text">
                <p>
                    Accept the <span class="yellow-text">CHALLENGE</span> of the popular <span class="yellow-text">SLOT</span> game project Kraken Casino.
                </p>
                <p>
                    This tournament lasts <span class="yellow-text">14 days</span>, where you can win and receive prizes every day. Each new day is a new challenge.
                </p>

                <div class="tournament__cond-title">Terms and Conditions:</div>
                <ul class="tournament_list">
                    <li>The winner of the tournament is the one who makes the maximum number of bets during the tournament <span class="yellow-text"> 14 days</span>.</li>
                    <li>All bets from <span class="yellow-text">{{$tournament->getMinBetString()}}</span> and above </li>
                    <li>Money rewards are credited to your account with the wager <span class="yellow-text">1x</span>. The winner is determined by automatic calculation. Minimum bet for participation in the tournament <span class="yellow-text"> {{$tournament->getMinBetString()}} </span>.</li>
                    <li>Prizes are automatically credited to the player's game account at the end of the tournament.</li>
                    <li>All cash prizes at the end of the tournament have a wagering requirement of 1x the amount.</li>
                    <li>All bonuses and winnings received as a result of winning the tournament must be wagered within 7 days from the date of issue. If the bonus wagering requirement is not met within the specified time period, the bonus amount, as well as any winnings received as a result of wagering the bonus and prize money, will be debited from the player's account due to the expiration date.</li>
                    <li>The list of winners will be displayed in the table after the calculation.</li>
                    <li>If at the end of the race the player's account is blocked or self-excluded, the winnings will not be paid out</li>
                    <li>If you have any questions about participation, please contact the support team, which is available 24/7</li>
                    <li>The money won in the tournament cannot be wagered on other types of games, except for slots!</li>
                    <li>In case of attempts to cheat and circumvent the rules, the Kraken Casino game project has the right to refuse the player further participation in the promotion.</li>
                    <li>The Kraken Casino game project may change the terms and conditions and end the competition at any time without giving reasons.</li>
                    <li>The table of winners will be updated in real time, where participants can follow the changes. </li>
                    <li>All other rules of the Kraken Casino game project remain in effect. </li>
                </ul>
            </div>
        </div>

    </div>
</div>
