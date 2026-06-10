<div id="reg-or-try-modal" style="display: none">
    <img src="/img/defaults/game.jpg" class="reg-or-try__game-image">

    <div class="reg-or-try__text mt-2">{{ __('games.regOrTryText') }}</div>
    <a href="{{ route('register', [], false) }}" data-ajax="modal" class="button button_green reg-or-try__join-button mt-1">{{ __('games.joinAndPlayForReal') }}</a>
    <a href="#" class="button button_outlined reg-or-try__try-button mt-1">{{ __('games.tryForFun') }}</a>
</div>
