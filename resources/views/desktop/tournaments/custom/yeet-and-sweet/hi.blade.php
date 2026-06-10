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
            <h4>09.02.2024 - 05.01.2025</h4>
        </div>
        <div class="tourn_stat_itm">
            <h5>पुरस्कार निधि</h5>
            <h4>$1,000,000</h4>
        </div>
        <div class="tourn_stat_itm">
            <h5>न्यूनतम बोली</h5>
            <h4>$0.2</h4>
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
                भाग लेने के लिए, खिलाड़ियों को इन-गेम विजेट में जॉइन पर क्लिक करना होगा और क्वालीफाइंग गेम खेलना होगा
                 €0.20 का न्यूनतम दांव या प्रति स्पिन इसकी मुद्रा के बराबर। खिलाड़ी इन-गेम लाइव पर अपने स्कोर का अनुसरण कर सकते हैं
                 लीडर बोर्ड. अपने स्कोर को वैध बनाने और लीडर बोर्ड पर प्रदर्शित करने के लिए, खिलाड़ी को इसे पूरा करना होगा
                 30 चक्करों का कम से कम एक चक्र। एक खिलाड़ी असीमित संख्या में चक्र पूरा कर सकता है, लेकिन केवल
                 उच्चतम परिणाम के साथ पूरा चक्र लीडर बोर्ड के लिए अर्हता प्राप्त करता है।<br>
                 "येट एंड स्वीट" केवल वास्तविक पैसे के साथ खेलने के लिए उपलब्ध है।<br><br>
                 येट एंड स्वीट टूर्नामेंट कैसे काम करते हैं?<br>
                 खिलाड़ियों को बस किसी भी योग्य खेल में विजयी दांव लगाना होगा और फिर उन्हें पुरस्कार दिया जाएगा
                 लीडर बोर्ड अंक. कृपया ध्यान दें कि Yeet&Sweet में कई गेम खेलने की अनुमति है, इसलिए खिलाड़ी
                 किसी भी समय केवल एक गेम खेल सकते हैं लेकिन किसी भी समय योग्य गेम के बीच स्विच कर सकते हैं।<br>
                 अंक प्रणाली 30 राउंड के चक्र के दौरान कुल संचित गुणकों पर आधारित है।
                 प्रति स्पिन अंक की गणना गुणक के रूप में की जाती है - प्रत्येक स्पिन में जीती गई राशि को राशि से विभाजित किया जाता है
                 दांव लगाया. उदाहरण के लिए: यदि खिलाड़ी €1 का दांव लगाता है और जीत €10 है तो इसका मतलब है कि जीत-गुणक 10 है। अंक हैं
                 जीत-गुणक को 100 से गुणा करके गणना की जाती है। इसलिए इस उदाहरण में, खिलाड़ी को 1000 अंक प्राप्त होंगे
                 जब जीत-गुणक 10 हो।<br>
                 कृपया ध्यान दें कि गैम्बल सुविधा इस गणना में योगदान नहीं देती है। केवल आधार से जीतता है
                 खेल गिना जाता है; गैंबल सुविधा का उपयोग करके अर्जित कोई भी अंक रद्द कर दिया जाएगा। उदाहरण के लिए, यदि कोई खिलाड़ी
                 €1 का दांव लगाता है और €10 जीतता है, और फिर जुआ सुविधा खेलकर जीत बढ़ाता है, केवल 1000 अंक होंगे
                 सम्मानित किया।
            </div>
        </div>
        <div id="pn_prizes" class="panes_tour_itm pane_prizes">
            <h4 style=" color: #fff; margin-bottom: 10px; ">प्रतियोगिता</h4>
            <table class="tournament__table" style="background-color: #0B0D31C0;width: 100%;">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>टूर्नामेंट 1</td>
                        <td>Sweetheart Dragon </td>
                    </tr>
                    <tr>
                        <td>टूर्नामेंट 2</td>
                        <td>Sweet Greens</td>
                    </tr>
                    <tr>
                        <td>टूर्नामेंट 3</td>
                        <td>Candy Blossoms</td>
                    </tr>
                    <tr>
                        <td>टूर्नामेंट 4</td>
                        <td>Lucky Loser</td>
                    </tr>
                    <tr>
                        <td>टूर्नामेंट 5</td>
                        <td>Joker’s Sweets</td>
                    </tr>
                    <tr>
                        <td>टूर्नामेंट 6</td>
                        <td>Fruitish</td>
                    </tr>
                    <tr>
                        <td>टूर्नामेंट 7</td>
                        <td>Golden Dunes</td>
                    </tr>
                    <tr>
                        <td>टूर्नामेंट 8</td>
                        <td>Sugar & Skulls</td>
                    </tr>
                    <tr>
                        <td>टूर्नामेंट 9</td>
                        <td>Candy Cane Cup</td>
                    </tr>
                    <tr>
                        <td>टूर्नामेंट 10</td>
                        <td>ULTIMATE 100</td>
                    </tr>
                </tbody>
            </table>
            <h4 style=" color: #fff; margin-bottom: 10px; margin-top: 30px;">पुरस्कार</h4>
            <table class="tournament__table" style="background-color: #0B0D31C0;width: 100%;">
                <thead>
                    <tr>
                        <th>पद</th>
                        <th>पुरस्कार</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>€31,000</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>€19,000</td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>€12,000</td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td>€6,000</td>
                    </tr>
                    <tr>
                        <td>5</td>
                        <td>€4,000</td>
                    </tr>
                    <tr>
                        <td>6</td>
                        <td>€3,600</td>
                    </tr>
                    <tr>
                        <td>7</td>
                        <td>€3,200</td>
                    </tr>
                    <tr>
                        <td>8</td>
                        <td>€2,800</td>
                    </tr>
                    <tr>
                        <td>9</td>
                        <td>€2,400</td>
                    </tr>
                    <tr>
                        <td>10</td>
                        <td>€2,000</td>
                    </tr>
                    <tr>
                        <td>10-20</td>
                        <td>€300</td>
                    </tr>
                    <tr>
                        <td>21-30</td>
                        <td>€250</td>
                    </tr>
                    <tr>
                        <td>31-40</td>
                        <td>€200</td>
                    </tr>
                    <tr>
                        <td>41-50</td>
                        <td>€150</td>
                    </tr>
                    <tr>
                        <td>51-100</td>
                        <td>€100</td>
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
