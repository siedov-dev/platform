@php
    $land_bonuses = [
             'welcome',
             'wheel',
             'slots',
             'shop',
//             'my_bonus',
             'daily',
             'achivements',
         ];
@endphp
<div class="bonuses_g_page">
    <h1 class="bonusg_title">Bonuses</h1>
    <div class="bonuses_g_block">
        @foreach ($land_bonuses as  $l_bonus)
            <a href="{{ route('bonuses.info', ['id' => $l_bonus]) }}" class="bn_g_card_link content-link">
                <div class="bn_g_card">
                    <div class="bg-img">
                        <img src="/img/bonuses/banners/{{$l_bonus}}.png">
                    </div>
                    <div class="bn_g_card_text">
                        <div class="bn_g_ct_name">{{__('bonuses.b-'.$l_bonus.'_title')}}</div>
                        <div class="bn_g_ct_amount">{!! __('bonuses.b-'.$l_bonus.'_prize') !!}</div>
                    </div>
                </div>
            </a>
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
        /*grid-template-columns: repeat(auto-fill, minmax(600px, 1fr));*/
        /*margin-top: 2rem;*/
        /*grid-gap: 1rem;*/
        justify-content: center;
        justify-items: center;
        width: 100%;
        flex-wrap: wrap;
    }
    .bn_g_card_link{
        /*width: clamp(600px, 100%, 800px);*/
        width: 48%;
        /*aspect-ratio: 16 / 6;*/
        margin: 1rem .5rem;
        min-height: 200px;
        /*min-width: 600px;*/
    }
    .bn_g_card{
        height: 100%;
        width: 100%;
        position: relative;
        background-color: #252A5E;
        border-radius: 1rem;
        overflow: hidden;
    }
    .bn_g_card .bg-img{
        /*position: absolute;*/
        top: 0;
        /*bottom: 0;*/
        /*left: 0;*/
        right: 0;
        z-index: 10;
        display: flex;
        justify-content: flex-end;
        height: 200px;
    }
    .bn_g_card .bg-img img{
        height: 100%;
        object-fit: contain;
    }
    .bn_g_card_text{
        position: absolute;
        top: 1rem;
        bottom: 1rem;
        left: 2rem;
        width: 40%;
        color: #FFF;
        z-index: 20;
        display: flex;
        justify-content: center;
        flex-flow: column;
    }
    .bn_g_ct_end{
        font-size: 12px;
    }
    .bn_g_ct_end span{
        font-weight: 600;
    }
    .bn_g_ct_name{
        font-size: 1.5rem;
        font-weight: 600;
        margin-top: 5px;
    }
    .bn_g_ct_amount{
        font-weight: 600;
        font-size: .875rem;
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
    /*.bn_g_card_link{*/
    /*    width: 49%;*/
    /*    text-decoration: none;*/
    /*    margin-bottom: 20px;*/
    /*}*/
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
