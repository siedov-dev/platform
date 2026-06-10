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
        display: block;
        justify-content: space-between;
        margin-top: 20px;
    }
    .tourn_stat_itm{
        background: #181833;
        color: #8C90CA;
        text-align: center;
        width: 100%;
        padding: 20px 0;
        border-radius: 10px;
        margin-bottom: 10px;
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
            <h4>05.02.2024 - 25.02.2024</h4>
        </div>
        <div class="tourn_stat_itm">
            <h5>पुरस्कार निधि</h5>
            <h4>$2,500,000</h4>
        </div>
        <div class="tourn_stat_itm">
            <h5>न्यूनतम बोली</h5>
            <h4>$0.2</h4>
        </div>
        <div class="tourn_stat_itm">
            <h5>न्यूनतम. स्पिन की संख्या</h5>
            <h4>150</h4>
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
                अपने खिलाड़ियों को प्यार और भाग्य के बवंडर में डुबो दें
                 इस वैलेंटाइन सीज़न में लव द जैकपॉट के साथ। सभी के पार
                 स्लॉट्स, आपके खिलाड़ी रहस्य के रोमांच का अनुभव करेंगे
                 ड्रॉप™, उन्हें तत्काल पुरस्कारों से लुभाता है<br>
                 • चरण शीर्षक: लव द जैकपॉट<br>
                 • प्रमोशन प्रकार: मिस्ट्री ड्रॉप™<br>
                 • पदोन्नति अवधि: 5 फरवरी-25<br>
                 • भाग लेने वाले खेल: सभी स्लॉट<br>
                 • विशेष गेम: 9 सिक्के™, 16 सिक्के™,
                 सूर्य की शक्ति™: सरोग, ताकतवर जंगली™: पैंथर<br>
                 • थीम: <br> के साथ वैलेंटाइन डे के सार को अपनाएं
                 दिल और होल्ड द जैकपॉट™ गेम्स का आकर्षण।
            </div>
        </div>
        
        <div id="pn_prizes" class="panes_tour_itm pane_prizes">
            <div class="article__text">तो यह पूरी तरह से यादृच्छिक है। हमारे पास बक्से हैं और हर बक्से में एक पुरस्कार है। हम नहीं जानते कि पुरस्कार कब घटेगा और उसका मूल्य क्या होगा।</div>
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
