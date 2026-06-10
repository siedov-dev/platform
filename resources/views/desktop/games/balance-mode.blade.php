<?php
/**
 * @var \App\GamesProviders\Game $game
 * @var \App\Models\Bonuses\UserBonus $bonus
 * @var \App\Models\Tournaments\TournamentGames\TournamentGame $tournamentGame
 * @var \App\MUser $user
 */

$user = auth()->user();
$currency = $user->getCurrencyName();
?>

<div id="balance-mode__form">
    <!-- Real money -->
    <div class="balance-mode__option">
        <div class="balance-mode__option-title">{{__('games.main_account')}}</div>
        <div class="balance-mode__option-value">{!! \App\Helpers\UI::renderMoney($user->getBalance(), $currency) !!}</div>
        <a href="{{ $game->getRealPlayUrl() }}?balance=main" data-name="{{ $game->getName() }}" class="balance-mode__button">{{__('games.play')}}</a>
    </div>

    <!-- Bonus money -->
    @if(isset($bonus))
        @if($bonus->isGameAllowed($game))
            <div class="balance-mode__option">
                <div class="balance-mode__option-title">{{__('games.bonus_account')}}</div>
                <div class="balance-mode__option-value">
                    @if($bonus->spins_available > 0)
                        {{ floor($bonus->spins_available) }}&nbsp;spins
                    @else
                        {!! \App\Helpers\UI::renderMoney($bonus->money_available, $currency) !!}
                    @endif
                </div>
                <a href="{{ $game->getRealPlayUrl() }}?balance=bonus" data-name="{{ $game->getName() }}" class="balance-mode__button">{{__('games.play')}}</a>
            </div>
        @else
            <div class="balance-mode__option" style="justify-content: center">
                <div class="balance-mode__option-title" style="text-align: center">{{__('games.gameIsNotAllowedForBonus')}}</div>
            </div>
        @endif
    @endif

    <!-- Tournament money -->
    @if(isset($tournamentGame))
        <div class="balance-mode__option" @if(!$tournamentGame->isGameAllowed($game)) style="opacity: 0.25; pointer-events: none;" @endif>
            <div class="balance-mode__option-title">{{__('games.tournament_account')}}</div>
            <div class="balance-mode__option-value">{!! \App\Helpers\UI::renderMoney($tournamentGame->getBalanceCents() / 100, 'USD') !!}</div>
            <a href="{{ $game->getRealPlayUrl() }}?balance=tournament" data-name="{{ $game->getName() }}" class="balance-mode__button">{{__('games.play')}}</a>
        </div>
    @endif
</div>
