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
        width: 100%;
        border: 1px solid #3c3b89;
    }
    .tournament__table td {
        padding: 0.5rem 1rem;
        color: #8888af;
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
        width: 80%;
        height: auto;
        margin: 0 auto;
    }
    .tournament__list li {
        margin: 1rem 0;
        font-size: 0.875rem;
    }
</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" class="tournament__banner-image">
    </div>


    <div class="article__text flex column">
        <div class="flex ai-center">
            <div class="tournament__cond-text">

                <div class="tournament__cond-title">नियम और शर्तें:</div>
                <ul class="tournament__list">
                    <li>लव नेटवर्क टूर्नामेंट के इवोप्ले स्पिन केवल वास्तविक मोड में खेलने के लिए उपलब्ध है</li>

                    <li>इवोप्ले नेटवर्क टूर्नामेंट बारहवें (12) सहमत दिनों के लिए चलता है
                        फरवरी 2022 का महीना। टूर्नामेंट 00:01 . के बीच होगा
                        यूटीसी 8 फरवरी 2022 और 19 फरवरी 2022 को 23:59 यूटीसी तक।</li>

                    <li>टूर्नामेंट का कुल पुरस्कार 12,000 यूरो है और इसे के बीच वितरित किया जाएगा
                        लीडरबोर्ड पर शीर्ष 100 उच्चतम स्कोरिंग खिलाड़ी।</li>

                    <li>टूर्नामेंट में भाग लेने के लिए आवश्यक न्यूनतम शर्त 0.20 EUR या . है
                        स्थानीय मुद्रा के बराबर। जितने अधिक अंक, खिलाड़ी लीडरबोर्ड में उतना ही ऊंचा उठता है।
                        लीडरबोर्ड पर पहले 100 खिलाड़ियों को पुरस्कार मिलेगा।</li>

                    <li>इस अभियान के लिए पुरस्कार पूल और न्यूनतम बोली EUR में निर्धारित की गई है और यह मुद्रा में उतार-चढ़ाव के अधीन हो सकती है।</li>
                </ul>
            </div>

        </div>

        <img src="/img/tournaments/spins-of-love/masks.png" alt="Gambling lady" class="tournament__image">

        <div class="flex ai-center mt-2">
            <div class="tournament__cond-text">
                <div class="mt-2"></div>
                <ul class="tournament__list">
                    <li>प्रत्येक स्पिन खिलाड़ी के कुल स्कोर में योगदान देगा, जो इस पर प्रदर्शित होता है
                        लीडरबोर्ड। कुल स्कोर की गणना स्कोर किए गए अंकों को जोड़कर की जाती है
                        भाग लेने वाले खेलों में खेले गए सभी राउंड में।</li>

                    <li>टूर्नामेंट का अपना योग्य मैकेनिक है: कुल जीत का संचय।</li>

                    <li>टूर्नामेंट में क्वालीफाइंग खेलों और एक पुरस्कार राशि (यूरो) की एक सूची है, जो
                        टूर्नामेंट के नियमों के अनुसार लीडरबोर्ड के विजेताओं के बीच वितरित किया गया।</li>

                    <li>यदि कई प्रतिभागी प्रक्रिया में समान अंक प्राप्त करते हैं
                        टूर्नामेंट के परिणामों की गणना, रैंकिंग में एक उच्च स्थान दिया जाता है
                        प्रतिभागी जिसने पहले अंक प्राप्त किए।</li>

                    <li>नकद पुरस्कार खिलाड़ियों के खाते में निकासी विकल्प के रूप में जमा किए जाएंगे
                        टूर्नामेंट के अंत के बाद धन।</li>

                    <li>Evoplay अंक शून्य करने या पुरस्कार का भुगतान नहीं करने का अधिकार सुरक्षित रखता है यदि सभी या
                        चालान का हिस्सा किसी भी स्पष्ट त्रुटि, त्रुटि या तकनीकी विफलता का परिणाम है
                        (गलत गेम पेआउट सहित), चाहे वह मशीन हो या मानव, किसी भी भाग लेने वाले गेम के संबंध में।</li>
                </ul>
            </div>
        </div>

        <img src="/img/tournaments/spins-of-love/sphere.png" alt="Gambling man" class="tournament__image">

        <div class="flex">
            <div class="tournament__cond-text">
                <div class="mt-4"></div>
                <ul class="tournament__list">
                    <li>Evoplay किसी भी समय टूर्नामेंट को बदलने, निलंबित करने या रद्द करने का अधिकार सुरक्षित रखता है
                        समय और पूर्व सूचना के बिना।</li>

                    <li>इनाम प्रणाली की गणना पुरस्कार राशि के अनुसार की जाती है और
                        जीत की तालिका के अनुसार निर्धारित किया गया है।</li>

                    <li><b>खेलों में भागीदारी:</b>
                        मिडनाइट शो, कैंडी ड्रीम्स, अनुबिस मून, इंडियाना की खोज,
                        फिरौन का अभिशाप, भाग्य की दौड़, ड्रैगन की मधुशाला, जंगल के सपने, प्रकाश का पेड़</li>

                    <li><b>टूर्नामेंट रेटिंग:</b> कुल लाभ का संचय।</li>

                    <li><b>यांत्रिकी:</b> सभी जीत का योग किया जाता है, जहां 1 अंक = 1
                        टूर्नामेंट की आधार मुद्रा की मौद्रिक इकाई। सभी परिवर्तित
                        टूर्नामेंट मुद्रा में किसी अन्य मुद्रा में मूल्य जीतना।
                        केवल अधिक या बराबर राशि पर बेट
                        टूर्नामेंट की न्यूनतम शर्त राशि।</li>
                </ul>
            </div>

        </div>

        <img src="/img/tournaments/spins-of-love/box.png" alt="Gambling girl" class="tournament__image">

        <div class="flex ai-center mt-2">
            <div class="tournament__cond-text" style="width: 100%">
                <table class="tournament__table mt-2">
                    <thead>
                    <tr>
                        <th>खिलाड़ी</th>
                        <th>इनाम</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr><td>1st</td><td>2 000 €</td></tr>
                    <tr><td>2nd</td><td>1 520 €</td></tr>
                    <tr><td>3rd</td><td>1 200 €</td></tr>
                    <tr><td>4th</td><td>800 €</td></tr>
                    <tr><td>5th</td><td>720 €</td></tr>
                    <tr><td>6th</td><td>640 €</td></tr>
                    <tr><td>7th</td><td>480 €</td></tr>
                    <tr><td>8th</td><td>352 €</td></tr>
                    <tr><td>9th</td><td>264 €</td></tr>
                    <tr><td>10th - 19th</td><td>160 €</td></tr>
                    <tr><td>20th - 49th</td><td>40 €</td></tr>
                    <tr><td>50th - 100th</td><td>24 €</td></tr>
                    <tr><td colspan="2" style="text-align: center"><b>कुल 12 000 €</b></td></tr>
                    </tbody>
                </table>
            </div>
        </div>



        <img src="/img/tournaments/spins-of-love/girl.png" alt="Gambling man" class="tournament__image mt-2">

    </div>



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

    @include('phone.footer')

</div>
