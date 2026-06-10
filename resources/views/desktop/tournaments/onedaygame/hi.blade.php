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
            <div class="tournament__cond-text">
                <p>
                    Accept the <span class="yellow-text">CHALLENGE</span> of the popular <span class="yellow-text">SLOT</span> game project Kraken Casino.
                </p>
                <p>
                    This tournament lasts 14 days, in which you can win daily and receive prizes. Each new day is a new challenge from a specific game.
                    30 slots for every day of the tournament, every day get a prize for beating the game.
                    Game of the Day reward: <span class="yellow-text">$3,000</span>
                    Win more than 10 times in the "game of the day" and get a super prize <span class="yellow-text">$15,000</span> at the end of the tournament!
                </p>

                <div class="tournament__cond-title">Terms and Conditions:</div>
                <ul class="tournament__list">
                    <li>The winner of the tournament is the one who makes the maximum number of bets per day (24 hours) in the "game of the day" the minimum bet that is taken into account from <span class="yellow-text">{{$tournament->getMinBetString() }}</span> per spin (everything from {{$tournament->getMinBetString()}} made in "Game of the Day" is summed up).</li>
                    <li>Money rewards are credited to your account with the wager <span class="yellow-text">10x</span>. The winner is determined by automatic calculation. </li>
                    <li><span class="yellow-text">14 slots</span> for each day, travel every day by participating in our tournament, get prizes every day. The minimum bet to participate in the tournament is <span class="yellow-text">{{$tournament->getMinBetString()}}</span>. (or equivalent in another currency)</li>
                    <li>A winner is determined daily. Tournament runs from <span class="yellow-text">{{$tournament->getStartDateTime()->isoFormat('MMMM D')}} to {{$tournament->getEndDateTime()->isoFormat('MMMM D')}}</span></li>
                    <li>Prizes are automatically credited to the player's game account at the end of the day for the "Game of the Day" within 24 hours.</li>
                    <li>All tournament prizes have a wagering requirement of <span class="yellow-text">10x the amount</span>.</li>
                    <li>All bonuses and winnings received as a result of winning the tournament must be wagered within <span class="yellow-text">7 days</span> from the date of issue. If the bonus wagering requirement is not met within the specified time period, the bonus amount, as well as any winnings received as a result of wagering the bonus and prize money, will be debited from the player's account due to the expiration date.</li>
                    <li>The list of "Game of the Day" winners will be displayed in the table after the calculations.</li>
                    <li>If at the end of the race the player's account is blocked or self-excluded, the winnings will not be paid out</li>
                    <li>If you have any questions about participation, please contact support, which is available <span class="yellow-text">24/7</span></li>
                    <li>Money won in the <span class="yellow-text">tournament cannot be wagered in ROULETTE!</span></li>
                    <li>In case of attempts to cheat and circumvent the rules, the Kraken Casino game project has the right to refuse the player further participation in the promotion. </li>
                    <li>The Kraken Casino game project may change the terms and conditions and end the competition at any time without giving reasons.</li>
                    <li>The table of winners will be updated in real time, where participants can follow the changes. </li>
                    <li>All other rules of the Kraken Casino game project remain in effect.</li>
                </ul>
            </div>

        </div>

    </div>
</div>
