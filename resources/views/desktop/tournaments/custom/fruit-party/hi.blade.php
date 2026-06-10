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
    .tournament__table {
        border-spacing: 0;
    }
    .tournament__table td {
        padding: 0.5rem 1rem;
        color: #8888af;
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
</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" class="tournament__banner-image">
    </div>

    <div class="article__text">
        <div class="tournament__cond-title">टूर्नामेंट की अवधि:</div>
        <div class="tournament__cond-text">10 से 17 फरवरी 2022 तक (दोनों दिन शामिल)</div>

    <div class="tournament__cond-title">GameArt फ्रूट पार्टी के नियम और शर्तें</div>
            <ul class="tournament__list">
                <div class="tournament__cond-text">खिलाड़ी निम्नानुसार वैधता अवधि के दौरान पदोन्नति में भाग ले सकता है:</div>
                <li>क्वालीफाइंग गेम खेलें और लीडरबोर्ड में अपने स्कोर पर नज़र रखें।</li>
                <li>क्वालीफाइंग गेम में असली पैसे से लगाए गए 1 यूरो के प्रत्येक दांव के लिए, खिलाड़ी को 1 अंक प्राप्त होता है।</li>
                <div class="tournament__cond-title">अतिरिक्त उपलब्धियां:</div>
                <li>बड़ी जीत - 50 अंक</li>
                <li>300 प्रतीक किसी भी खेल में तितर बितर - 300 अंक</li>
                <li>महाकाव्य जीत - 500 अंक</li>
                <li>5000 स्पिन – 1000 अंक (शर्त मूल्य €0,40 या अधिक)</li>
                <li>वह लीडरबोर्ड लाइव होगा और गेम में ही प्रदर्शित होगा।</li>
            </ul>

        <div class="tournament__cond-title">यांत्रिकी:</div>
        <div class="tournament__cond-text">
            <ul class="tournament__list">
                <li>फ्रूट पार्टी गेमआर्ट एक वैश्विक टूर्नामेंट है जो सभी खिलाड़ियों के लिए उपलब्ध है।</li>
                <li>टूर्नामेंट गुरुवार 10 फरवरी को 00:01 GMT से शुरू होगा और 23:59 GMT पर समाप्त होगा।
                       गुरुवार 17 फरवरी 2022 को।</li>
                <li>खिलाड़ी निम्नलिखित पुरस्कार जीत सकते हैं: € 10,000 को 150 खिलाड़ियों के बीच वितरित किया जाएगा</li>
                <li>कंपनी किसी भी समय पदोन्नति को बदलने, रद्द करने या समाप्त करने का अधिकार सुरक्षित रखती है।</li>
            </ul>


            <table class="tournament__table mt-2">
                <thead>
                <tr>
                    <th>Player</th>
                    <th>Prize</th>
                </tr>
                </thead>
                <tbody>
                <tr><td>1st</td><td>2,500 €</td></tr>
                <tr><td>2nd</td><td>1,000 €</td></tr>
                <tr><td>3rd</td><td>600 €</td></tr>
                <tr><td>4th</td><td>300 €</td></tr>
                <tr><td>5th -14th</td><td>100 €</td></tr>
                <tr><td>15th-24th</td><td>80 €</td></tr>
                <tr><td>25th-50th</td><td>50 €</td></tr>
                <tr><td>51st-150th</td><td>25 €</td></tr>
                </tbody>
            </table>
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
