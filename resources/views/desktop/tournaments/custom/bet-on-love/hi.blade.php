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
        width: 40%;
        height: auto;
    }
    .tournament__list li {
        margin: 1rem 0;
        font-size: 0.875rem;
    }
    .tourn_stats{
        display: flex;
        justify-content: space-between;
        margin-top: 20px;

    }
    .tourn_stat_itm{
        background: #181833;
        color: #8C90CA;
        text-align: center;
        width: 24%;
        padding: 20px 0;
        border-radius: 10px;
    }
    .tourn_stat_itm h5{
        font-size: 12px;
        font-weight: 400;
        margin-bottom: 15px;
    }
    .tourn_stat_itm h4{
        color: #FFC660;
        font-size: 16px
    }
    .tabs_tour{
        display: flex;
        background: #181833;
        margin-top: 30px;
        padding: 5px;
        justify-content: space-between;
    }
    .tabs_tour button{
        width: 32%;
        background: transparent;
        border: none;
        padding: 7px;
        border-radius: 5px;
        color: #8C90CA;
        cursor: pointer;
    }
    .tabs_tour button.active{
        background: #2B2F5B;
        color: #fff;
    }
    .tabs_tour button:hover{
        background: #2B2F5B;
        color: #fff;
    }
    .panes_tour{
        margin-top: 30px;
    }
    .panes_tour_itm{
        display: none;
    }
    .panes_tour_itm.active{
        display: block;
    }
</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" class="tournament__banner-image" style="height: auto;">
    </div>

    <div class="tourn_stats">
        <div class="tourn_stat_itm">
            <h5>टूर्नामेंट की अवधि</h5>
            <h4>06.02.2024 - 15.02.2024</h4>
        </div>
        <div class="tourn_stat_itm">
            <h5>पुरस्कार निधि</h5>
            <h4>€ 50,000</h4>
        </div>
        <div class="tourn_stat_itm">
            <h5>न्यूनतम बोली</h5>
            <h4>€ 0.40</h4>
        </div>
        <div class="tourn_stat_itm">
            <h5>न्यूनतम. स्पिन की संख्या</h5>
            <h4>100</h4>
        </div>
    </div>

    <div class="tabs_tour">
        <button id="pt_games" class="tabs_tourbtn active" data-type="games">खेल</button>
        <button id="pt_terms" class="tabs_tourbtn" data-type="terms">नियम और शर्तें</button>
        <button id="pt_prizes" class="tabs_tourbtn" data-type="prizes">पुरस्कार</button>
    </div>
    <div class="panes_tour">
        <div id="pn_games" class="panes_tour_itm pane_games active">
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
        </div>
        <div id="pn_terms" class="panes_tour_itm pane_terms">
            <div class="article__text">
                1. इवोप्ले नेटवर्क अभियान केवल वास्तविक मोड में खेलने के लिए उपलब्ध है
                 इसमें दो टूर्नामेंट एक साथ होते हैं: बेट ऑन लव
                 नेटवर्क टूर्नामेंट और बेट ऑन लव प्राइज़ ड्रॉप्स अभियान।<br>
                 2. इवोप्ले नेटवर्क अभियान दस (10) सहमत दिनों तक चलता है और होता है
                 6 फरवरी 2024 को 00:01 यूटीसी और 15 फरवरी 2024 के बीच 23:59 बजे तक
                 यूटीसी.<br>
                 3. टूर्नामेंट की कुल पुरस्कार राशि €50,000 है।
                 4. इसमें भाग लेने वाले सभी ऑपरेटरों के लिए लीडरबोर्ड समेकित है
                 नेटवर्क अभियान.<br>
                 5. चालान से पहले सभी पुरस्कार जीजीआर से काट लिए जाते हैं, चाहे कुछ भी हो
                 अनुबंध के अनुसार बोनस कटौती का प्रतिशत.<br>
                 6. इस अभियान के लिए पुरस्कार पूल और न्यूनतम शर्त EUR में निर्धारित हैं
                 मुद्रा रूपांतरण में उतार-चढ़ाव के अधीन हो सकता है।<br>
                 7. अभियान में योग्य खेलों की एक सूची और एक पुरस्कार पूल (EUR) है, जो
                 टूर्नामेंट के नियमों के अनुसार विजेताओं के बीच वितरित किया जाता है।<br>
                 8. प्रत्येक योग्य स्पिन खिलाड़ी के कुल स्कोर में योगदान देगा
                 लीडरबोर्ड पर दिखाया गया. कुल स्कोर की गणना अंकों के साथ की जाती है
                 भाग लेने वाले खेलों में खेले गए सभी राउंड के दौरान लाभ प्राप्त हुआ। जितने अधिक अंक,
                 खिलाड़ी लीडरबोर्ड पर उतना ही ऊपर जाता है।<br>
                 9. इवोप्ले के पास स्कोर रद्द करने, या पुरस्कार का भुगतान न करने का अधिकार सुरक्षित है
                 संपूर्ण या आंशिक स्कोर किसी स्पष्ट त्रुटि, गलती या तकनीकी परिणाम के कारण होता है
                 किसी मशीन या मानव के कारण हुई गलती (गलत गेम भुगतान सहित)।
                 भाग लेने वाले किसी भी खेल में त्रुटि।<br>
                 10. इवोप्ले आगे स्कोर रद्द करने, या पुरस्कार का भुगतान न करने का अधिकार सुरक्षित रखता है
                 जहां, राय में, स्कोर का पूरा हिस्सा धोखाधड़ी या मिलीभगत से उत्पन्न होता है
                 अन्य खिलाड़ियों के साथ.<br>
                 11. मुआवज़ा प्रणाली की गणना पुरस्कार के अनुसार की जाती है
                 भाग लेने वाले नेताओं की पूल और निर्धारित संख्या।<br>
                 12. एक कैसीनो को सूची को लागू करके टूर्नामेंट का समर्थन करना चाहिए
                 विपणन गतिविधियाँ नीचे दी गई हैं।<br>
                 13. खिलाड़ियों को इसकी शुरुआत के बारे में एक पॉप-अप घोषणा प्राप्त होगी
                 टूर्नामेंट से दो (2) दिन पहले।<br>
                 14. टूर्नामेंट में अपनी भागीदारी कम से कम सात (7) दिनों के लिए जमा करें
                 टूर्नामेंट शुरू होने से पहले.
            </div>
        </div>
        <div id="pn_prizes" class="panes_tour_itm pane_prizes">
            <h4 style="color: #fff; margin-bottom: 15px;">हॉल ऑफ विन नेटवर्क टूर्नामेंट</h4>
            <table class="tournament__table" style="background-color: #0B0D31C0;width: 100%;">
                <thead>
                <tr>
                    <th>जगह</th>
                    <th>पुरस्कार</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>1</td><td>€ 3000</td>
                </tr>
                <tr>
                    <td>2</td><td>€ 2800</td>
                </tr>
                <tr>
                    <td>3</td><td>€ 2450</td>
                </tr>
                <tr>
                    <td>4</td><td>€ 2100</td>
                </tr>
                <tr>
                    <td>5</td><td>€ 1750</td>
                </tr>
                <tr>
                    <td>6</td><td>€ 1400</td>
                </tr>
                <tr>
                    <td>7</td><td>€ 1155</td>
                </tr>
                <tr>
                    <td>8</td><td>€ 980</td>
                </tr>
                <tr>
                    <td>9</td><td>€ 910</td>
                </tr>
                <tr>
                    <td>10</td><td>€ 455</td>
                </tr>
                <tr>
                    <td>11-20</td><td>€ 280</td>
                </tr>
                <tr>
                    <td>21-50</td><td>€ 140</td>
                </tr>
                <tr>
                    <td>51-100</td><td>€ 105</td>
                </tr>
                <tr>
                    <td>101-150</td><td>€ 70</td>
                </tr>
                <tr>
                    <td>151-200</td><td>€ 35</td>
                </tr>
                <tr>
                    <td ></td>
                    <td><span style="color: red; text-transform: uppercase; font-weight: bold">€ 35000</span></td>
                </tr>
                </tbody>
            </table>
            <h4 style="margin-top: 30px; color: #fff; margin-bottom: 15px;">हॉल ऑफ विन प्राइज ड्रॉप्स अभियान</h4>
            <table class="tournament__table" style="background-color: #0B0D31C0;width: 100%;">
                <thead>
                <tr>
                    <th>मात्रा</th>
                    <th>पुरस्कार</th>
                </tr>
                </thead>
                <tbody>
          
                <tr><td>1</td><td>€ 65</td></tr>
                <tr><td>2</td><td>€ 45</td></tr>
                <tr><td>7</td><td>€ 35</td></tr>
                <tr><td>10</td><td>€ 20</td></tr>
                <tr><td>20</td><td>€ 15</td></tr>
                <tr><td>60</td><td>€ 10</td></tr>
             
                <tr>
                    <td></td>
                    <td><span style="color: red; text-transform: uppercase; font-weight: bold">€ 15000</span></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>


  

    <script>
        document.getElementById("pt_games").addEventListener("click", function(){
            console.log('asdasd');
    
            let allElements = document.querySelectorAll('.panes_tour_itm');
            allElements.forEach((element) => {
                element.classList.remove('active');
            });

            let allElement = document.querySelectorAll('.tabs_tourbtn');
            allElement.forEach((el) => {
                el.classList.remove('active');
            });
    
            let pane = document.getElementById("pn_games");
            pane.classList.add('active');

            let but = document.getElementById("pt_games");
            but.classList.add('active');
            //panes_tour_itm.pane_
    
        });
        document.getElementById("pt_terms").addEventListener("click", function(){
            console.log('asdasd');
    
            let allElements = document.querySelectorAll('.panes_tour_itm');
            allElements.forEach((element) => {
                element.classList.remove('active');
            });

            let allElement = document.querySelectorAll('.tabs_tourbtn');
            allElement.forEach((el) => {
                el.classList.remove('active');
            });
    
            let pane = document.getElementById("pn_terms");
            pane.classList.add('active');

            let but = document.getElementById("pt_terms");
            but.classList.add('active');
            //panes_tour_itm.pane_
    
        });
        document.getElementById("pt_prizes").addEventListener("click", function(){
            console.log('asdasd');
    
            let allElements = document.querySelectorAll('.panes_tour_itm');
            allElements.forEach((element) => {
                element.classList.remove('active');
            });

            let allElement = document.querySelectorAll('.tabs_tourbtn');
            allElement.forEach((el) => {
                el.classList.remove('active');
            });
    
            let pane = document.getElementById("pn_prizes");
            pane.classList.add('active');

            let but = document.getElementById("pt_prizes");
            but.classList.add('active');
            //panes_tour_itm.pane_
    
        });
    </script>
    @include('desktop.footer')

</div>
