<style>
    .bonus-gift-box{
        position: fixed;
        right: 2rem;
        bottom: 10rem;
        display: flex;
        flex-flow: column;
        align-items: center;
        z-index: 10;
    }
    .bonus-gift-box .hide-bonus-gift{
        display: flex;
        width: 100%;
        padding: 0.2rem;
        border-radius: 5px;
        justify-content: center;
        background-color: #2C2C5D;
        text-decoration: none !important;
        font-weight: 500;
        line-height: 1rem;
        font-size: 0.625rem;
        color: #8283C0;
        cursor: pointer;
    }
    @media (max-width: 800px) {
        .bonus-gift-box {
            right: 1rem;
            bottom: 7rem;
        }
        .bonus-gift-box .bonus-gift-anim{
            background-color: #1C1646;
            border-radius: 50%;
        }
        .bonus-gift-box .hide-bonus-gift{
            margin-top: 0.5rem;
        }
    }
</style>
<div class="bonus-gift-box">
    <a href="{{route('cabinet.bonuses', false, [])}}">
        @include('common.blocks.bonus-gift-anim', ['currency' => $currency])
    </a>
    <div class="hide-bonus-gift" onclick="this.parentElement.style.display='none'">{{__('bonus.hide')}}</div>
</div>
