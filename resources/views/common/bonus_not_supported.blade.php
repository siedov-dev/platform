<style>
    .bonus-not-support{
        display: flex;
        flex-flow: column;
        justify-content: space-evenly;
        align-items: center;
        background-color: white;
        color: black;
        max-width: 400px;
        padding: 2rem;
    }
    .bonus-not-support img{
        border-radius: 1rem;
    }
    .bonus-not-support p{
        color: #3d3e6d;
        font-weight: 700;
        font-size: .875rem;
        text-indent: 1.5em;
        line-height: 1rem;
        text-align: justify;
    }
</style>
<div class="bonus-not-support">
    <img src="{{$game->getImageUrl()}}" class="mb-2" width="190px" height="256px" style="object-fit: cover;">
    <p>{{__('bonus.not_supported', ['bonus_name' => $bonus->getName()])}}</p>
    <div class="flex w-100 jc-sb p-1 ml-2 mr-2">
        <a href="{{ $game->getRealPlayUrl() }}" data-name="{{ $game->getName() }}" class="balance-mode__button modal__button btn-danger">{{__('games.play')}}</a>
        <button class="modal__button_outlined mt-1" onclick="this.closest('.modal').querySelector('.close-button').click()">{{__('cabinet.cancel')}}</button>
    </div>
</div>
