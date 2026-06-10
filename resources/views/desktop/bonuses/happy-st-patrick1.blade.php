<div class="bonuses_g_page">
    <div class="bonuses_g_bread">
        <ul>
            <li><a href="/">Kraken Casino</a></li>
            <li><a href="{{ route('bonuses_guest') }}">Бонуси та акції</a></li>
            <li><a class="active">Бонуси</a></li>
        </ul>
    </div>
    <div class="bonus_g_banner" style="background-image: url(/img/bonuses/happy-st-patrick1_banner.png)">
        <div class="bonus_g_b_text">
            <div class="bonus_g_b_end">
                Ends in
                <img src="/img/time_ico_black.svg" alt="End date">
                {{ \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($bonus['end'])) }} days 
            </div>
            <div class="bonus_g_b_title">
                <h1>Назва бонусу до 50 символів</h1>
                <p>Happy St. Patrick Days!!!</p>
            </div>
            <div class="bn_g_ct_view">Participate!</div>
        </div>
    </div>
    <div class="bonus_g_content">
        <h2>Потрібно більше призів!</h2>
        <p>
            Британський провайдер Pragmatic Play починає атракціон небаченої щедрості! Їхній турнір Drops and Wins тепер роздає не 500.000 EUR, а відразу 2.000.000 EUR на місяць!<br>
            Встигніть виграти в останньому квітневому турнірі до 25.000 євро та зловити до 2.500 євро на день на SlotsCity.ua! Щедра злива призів триватиме майже рік:
        </p>
    
        <p class="m-0"><b>Період дії акції:</b></p>
        <h3>до 6 березня 2024 року</h3>
        <p class="m-0"><b>Призовий фонд кожного місяця:</b></p>
        <h3>2.000.000 EUR</h3>
        <p>
            Робіть ставки реальними грошима від 18,5 грн і вище в іграх провайдера, що беруть участь в акції.<br>
            Набирайте бали за перевищення виграшами ставок (X-Win).<br>
            Отримуйте призи у вигляді грошових сум, фріспінів, множників виграшів.<br>
            Лідери акції отримують призи за підсумками кожного тижня від 10 до 25.000 EUR або множники виграшу від 10х до 25.000х.<br>
            Крім того, щодня нараховуються випадковим чином по 1.700 призів від 10 до 2.500 EUR на загальну суму 35.000 EUR (за курсом).<br>
            Під час використання множників і фріспінів застосовується ставка не більше 10 EUR (за курсом).
        </p>
        <h2>Ігри, що беруть участь у турнірі:</h2>
        <p>Wolf Gold, Mustang Gold, Sweet Bonanza, John Hunter and the Tomb of the Scarab Queen, Great Rhino Megaways, John Hunter and the Book of Tut, The Dog House, The Dog House Megaways, Chilli Heat, Fruit Party, Big Bass Bonanza, Madame Destiny Megaways, Gems Bonanza, Wild Wild Riches, Wild West Gold, Gates of Olympus, Power of Thor Megaways, Floating Dragon Hold&Spin, The Hand of Midas, Buffalo King Megaways, Juicy Fruits, Bigger Bass Bonanza, Starlight Princess, Joker’s Jewels, Big Bass Bonanza Megaways, Book of The Fallen, 5 Lions Megaways, Buffalo King, Cleocatra, Extra Juicy Megaways, Wild West Gold Megaways, Big Bass Splash, Sugar Rush, Big Bass – Keeping it Reel, Release the Kraken 2, Fish Eye, Monster Superlanche, Fire Archer, Club Tropicana, Wild Wild Riches Megaways, Wild West Duels, The Dog House Multihold, Cowboy Coins, Mochimon, Jasmine Dreams, The Knight King, Big Bass Bonanza – Hold & Spinner, The Red Queen, Gods of Giza</p>
    </div>

    <h2 class="other_bonus_title">Інші бонуси</h2>
    <div class="bonuses_g_block">
        @foreach ($bonuses as $key => $bonus)
            <a href="{{ route('bonuses_guest.show', $key) }}" class="bn_g_card_link">
                <div class="bn_g_card" style="background-image: url(/img/bonuses/{{$key}}_tile.png)">
                    <div class="bn_g_card_text">
                        <div class="bn_g_ct_end">
                            <img src="/img/time_ico.svg" alt="End date">
                            Ends in <span>{{ \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($bonus['end'])) }} days</span>
                        </div>
                        <div class="bn_g_ct_name">{{ $bonus['name'] }}</div>
                        <div class="bn_g_ct_amount">{{ $bonus['amount'] }}</div>
                        <div class="bn_g_ct_view">Participate!</div>
                    </div>
                </div>
            </a>
            @if($loop->iteration == 2) @php break @endphp @endif
        @endforeach
    </div>
</div>

<style>
    .bonuses_g_page{
    margin-top: 30px;
}
.bonusg_title{
    font-size: 36px;
    color: #fff;
}
.bonuses_g_block{
    display: flex;
    flex-direction: row;
    margin-top: 30px;
    flex-wrap: wrap;
    justify-content: space-between;
}
.bn_g_card{
    min-height: 230px;
    background-size: cover;
    border-radius: 12px;
    background-position: center;
}
.bn_g_card_text{
    width: 50%;
    padding: 30px;
    color: #Fff;
    padding-top: 50px;
}
.bn_g_ct_end{
    font-size: 12px;
}
.bn_g_ct_end span{
    font-weight: 600;
}
.bn_g_ct_name{
    margin-top: 15px;
    font-weight: 600;
    font-size: 14px;
}
.bn_g_ct_amount{
    font-size: 21px;
    font-weight: 600;
    margin-top: 5px;
}
.bn_g_ct_view{
    background-color: #A7DA94;
    padding: 14px 35px;
    border-radius: 100px;
    margin-top: 21px;
    display: inline-block;
    color: #000;
    font-weight: 600;
    font-size: 12px;
}
.bn_g_card_link{
    width: 49%;
    text-decoration: none;
    margin-bottom: 20px;
}
.bonus_g_banner{
    background-size: cover;
    min-height: 300px;
    border-radius: 17px;
}
.bonus_g_b_text{
    width: 50%;
    padding: 40px;
    color: #fff;
}
.bonus_g_b_end{
    color: #000;
    background: #fff;
    padding: 7px 20px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    border-radius: 8px;
}
.bonus_g_b_end img{
    margin: 0 5px;
}
.bonuses_g_bread{
    margin-bottom: 30px;
}
.bonuses_g_bread ul{
    padding: 0;
    margin: 0;
    display: flex;
}
.bonuses_g_bread ul li{
    margin-right: 10px;
    list-style-type: none;
}
.bonuses_g_bread ul li a{
    border: 1px solid #7a82b442;
    background: #1C1F3E;
    padding: 5px 10px;
    border-radius: 7px;
    color: #7A82B4;
    font-size: 14px;
    text-decoration: none;
}
.bonuses_g_bread ul li a.active{
    color: #fff;
}
.bonus_g_b_title{
    margin-top: 30px;
}
.bonus_g_b_title h1{
    font-size: 40px;
}
.bonus_g_b_title p{
    font-size: 14px;
    margin-top: 10px;
}
.bonus_g_content{
    margin-top: 30px;
}
.bonus_g_content h2{
    color: #fff;
    font-size: 32px;
    margin-bottom: 15px;
}
.bonus_g_content h3{
    color: #A7DA94;
    font-size: 24px;
    margin-bottom: 15px;
}
.bonus_g_content p{
    color: #7A82B4;
    margin-bottom: 20px;
    font-size: 14px;
    line-height: 21px;
}
.bonus_g_content p.m-0{
    margin: 0;
}
.other_bonus_title{
    color: #fff;
    font-size: 32px;
    margin-bottom: 15px;
}
</style>