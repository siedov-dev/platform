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
        color: #fff;
    }
</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" style="width: 100%;">
    </div>

    <div class="article__text">
        <div class="tournament__cond-title">नेटवर्क अभियान अवधि:</div>
        <div class="tournament__cond-text"><strong>25.12.2023 00:00 UTC</strong> से <strong>01.01.2024 23:59 UTC</strong></div>

        <div style="display: flex; margin-top: -150px; justify-content: flex-end;">
            <img src="/img/tournaments/winter-lucky-drops/pngegg.png" alt="grass" style="width: 280px; height: 320px; margin-bottom: -180px;">
        </div>


        <div class="tournament__cond-title"><span style="color: red; font-weight: bold">गर्म अवधि</span> - From 29.12.2023 15:00 UTC</div>

        <div class="tournament__cond-title">3 ओक्स नेटवर्क टूर्नामेंट नियम और शर्तें:</div>
        <div class="tournament__cond-text">
            <p>
                1. नेटवर्क लकी ड्रॉप्स में भाग लेकर, आप यहां उल्लिखित नियमों और शर्तों और द्वारा प्रदान की गई शर्तों से सहमत हैं
                 ऑपरेटर.<br>
                 2. आपातकालीन स्थिति में, 3 ओक्स गेमिंग के पास भुगतान सहित किसी भी समय लकी ड्रॉप्स प्रोमो को रद्द करने का अधिकार है।
                 यदि आवश्यक समझा जाए तो जीतें।<br>
                 3. केवल रियल मनी राउंड ही लकी ड्रॉप्स अभियान में भाग ले सकते हैं।<br>
                 4. स्पिन बनाने की कीमत के अलावा, नेटवर्क लकी ड्रॉप्स में प्रवेश करने के लिए कोई अतिरिक्त लागत नहीं है।<br>
                 5. नेटवर्क लकी ड्रॉप्स नियम कभी भी बदले जा सकते हैं।<br>
                 6. खराबी के कारण सभी भुगतान रद्द हो जाते हैं।
            </p>
        </div>


        <div class="tournament__cond-title">योग्यता:</div>
        <div class="flex">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>खिलाड़ी को किसी भी भाग लेने वाले खेल में शामिल होना होगा या उसमें शामिल होना होगा।</li>
                     <li>लकीड्रॉप प्राप्त करने में सक्षम होने के लिए आवश्यक न्यूनतम शर्त <span style="color: red; फ़ॉन्ट-वेट: बोल्ड">EUR 0.50</span> या भाग लेने वाले गेम में उपलब्ध मुद्राओं में समतुल्य राशि है( एस).</li>
                     <li>किसी भी भाग लेने वाले खेल पर लगाए गए प्रत्येक क्वालीफाइंग दांव को पदोन्नति अवधि के दौरान पुरस्कार पूल से एक यादृच्छिक पुरस्कार मिल सकता है।</li>
                     <li>प्रत्येक खिलाड़ी अधिकतम 3 पुरस्कार जीत सकता है।</li>
                </ul>
            </div>
            <img src="/img/tournaments/winter-lucky-drops/snowman.png" alt="girl" style="margin-left: 1rem; width: 300px;">
        </div>

        <div class="tournament__cond-title">यांत्रिकी जीतो:</div>
        <div class="tournament__cond-text">
            <div class="tournament__list">
                <span style="color: red; text-transform: uppercase; font-weight: bold"> लकी ड्रॉप्स - खिलाड़ियों को इवेंट अवधि के दौरान किसी भी समय नकद पुरस्कार जीतने की संभावना प्रदान करता है। पुरस्कार यादृच्छिक रूप से दिए जाते हैं, और सभी खिलाड़ियों के पास प्रत्येक क्वालीफाइंग स्पिन में जीतने का समान मौका होता है।
            </div>
        </div>
        <div class="tournament__cond-title">खेल भाग ले रहे हैं:</div>
        <div class="tournament__cond-text"><span style="color: red; font-weight: bold">सभी खेल</span></div>
        <div class="tournament__cond-title">कीमत पूल:</div>

        <ul class="tournament__list">
            <div class="tournament__cond-text">इस अभियान के लिए पुरस्कार पूल 30,000 यूरो (या खिलाड़ी की मुद्रा समतुल्य) है जो निम्नलिखित संरचना द्वारा कई पूलों से संयुक्त है:</div>
            <div class="tournament__cond-text"><span style="color: red; font-weight: 600">30,000 EUR</span> के लिए (25.12.2023 00:00 UTC to 01.01.2023 23:59 UTC)</div>
            <li class="tournament__cond-text"><span style="color: red; font-weight: 600">30 000 EUR</span> कुल मिलाकर के लिए <span style="color: red; font-weight: 600">प्रत्येक सप्ताह</span></li>
        </ul>


        <div class="flex ai-center">

            <table class="tournament__table"
                   style="background: url('/img/tournaments/spring-lucky-drops/1.png') no-repeat center; background-size: cover; box-shadow: #30ff00 0 0 7px; z-index: 2;">
                <thead>
                <tr>
                    <th>मात्रा</th>
                     <th>पुरस्कार मूल्य</th>
                     <th>योग</th>
                </tr>
                </thead>
                <tbody>
                    <tr><td>15</td><td>€ 200</td><td>€ 3,000</td></tr>
                    <tr><td>30</td><td>€ 100</td><td>€ 3,000</td></tr>
                    <tr><td>60</td><td>€ 75</td><td>€ 4,500</td></tr>
                    <tr><td>80</td><td>€ 60</td><td>€ 4,800</td></tr>
                    <tr><td>105</td><td>€ 50</td><td>€ 5,250</td></tr>
                    <tr><td>130</td><td>€ 30</td><td>€ 3,900</td></tr>
                    <tr><td>150</td><td>€ 20</td><td>€ 3,000</td></tr>
                    <tr><td>170</td><td>€ 15</td><td>€ 2,550</td></tr>
                    <tr>
                        <td colspan="2"></td>
                        <td><span style="color: red; text-transform: uppercase; font-weight: bold">€ 30,000</span></td>
                    </tr>
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
