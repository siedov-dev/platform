<style>
    .bonus-gift-anim{
        position: relative;
        width: 72px;
        height: 72px;
    }
    .bonus-gift-anim img{
        position: absolute;
        bottom: 0;
        left: 0;
        z-index: 2;
        animation-name: bonus-gift-img-anim;
        animation-duration: 5s;
        animation-direction: alternate;
        animation-iteration-count: infinite;
        animation-timing-function: ease-out;
    }
    .bonus-gift-anim .currency-icon{
        position: absolute;
        left: calc(50% - 13px);
        bottom: 15px;
        z-index: 1;
        animation-name: bonus-gift-curr-anim;
        animation-duration: 5s;
        animation-direction: alternate;
        animation-iteration-count: infinite;
        animation-timing-function: ease-out;
    }

    @keyframes bonus-gift-img-anim {
        0%, 70% {transform: scale(1);}
        80%, 100% {
            transform: scale(0.5) translateY(20px);
        }
    }

    @keyframes bonus-gift-curr-anim {
        0%, 70% {transform: scale(1)}
        80%, 100% {
            transform: scale(1.4) translateY(-10px);
        }
    }
</style>
<div class="bonus-gift-anim">
    <img src="/img/icons/gift2.png">
    <div class="currency-icon currency-icon_{{mb_strtolower($currency??'RUB')}}"></div>
</div>
