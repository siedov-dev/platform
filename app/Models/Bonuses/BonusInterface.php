<?php

namespace App\Models\Bonuses;

use App\GamesProviders\Game;
use App\GamesProviders\GamesCollection;
use App\Models\Bonuses\Contracts\BonusRenderer;
use App\MUser;
use Carbon\Carbon;

/**
 * Any bonus must implement this interface (no matter who is provider of the bonus).
 */
interface BonusInterface
{
    /**
     * Returns ID of the bonus.
     * @return int
     */
    public function getId(): ?int;

    /**
     * Returns name of the bonus.
     * @return string
     */
    public function getName(): string;

    /**
     * Returns description of the bonus.
     * Should contain 150-300 symbols.
     * @return string
     */
    public function getDescription(): string;

    /**
     * Returns text that can be displayed at the progress bar.
     * @return string
     */
    public function getProgressHint(): string;

    /**
     * Checks if bonus can has progress.
     * @return mixed
     */
    public function isProgressAvailable(): bool;

    /**
     * Returns bonus progress in percents [0 - 100].
     * @return int
     */
    public function getProgressPercents(): int;

    /**
     * Returns true if bonus will be activated just after user agreement.
     * Please note that agreement request must be handled before payment request.
     * @return bool
     * @see isPaymentRequired
     */
    public function isAgreementRequired(): bool;

    /**
     * Returns true if bonus will be activated just after next payment.
     * Please note that agreement request must be handled before payment request.
     * @return bool
     * @see isAgreementRequired
     */
    public function isPaymentRequired(): bool;

    /**
     * Returns amount of credits that is required for bonus activation.
     * @return float
     * @see isPaymentRequired
     */
    public function getRequiredSum(): float;

    /**
     * Checks if bonus can be cancelled.
     * @return bool
     */
    public function isCancelable(): bool;

    /**
     * Checks if bonus is active.
     * @return bool
     */
    public function isActive(): bool;

    /**
     * Checks if bonus is done.
     * @return bool
     */
    public function isDone(): bool;

    /**
     * Checks if bonus is completed (done, expired, canceled, etc.).
     * @return bool
     */
    public function isCompleted(): bool;

    /**
     * Checks if bonus is canceled.
     * @return bool
     */
    public function isCanceled(): bool;

    /**
     * Checks if bonus is expired.
     * @return bool
     */
    public function isExpired(): bool;

    /**
     * Checks if bonus is paused.
     * @return bool
     */
    public function isPaused(): bool;

    public function canPause(): bool;

    /**
     * Returns expiration date.
     * @return Carbon
     */
    public function getExpirationDate(): Carbon;

    /**
     * Returns all money that user won (including money that already paid out).
     * @return float
     */
    public function getTotalMoneyWon(): float;

    /**
     * Returns money available for payout.
     * @return float
     */
    public function getMoneyToPayout(): float;

    /**
     * Return money available for playback bonuses
     * like NonDepositBonus
     * @return float
     */
    public function getMoneyAvailableForPlay(): float;

    /**
     * Checks if there are additional options on activation
     * @return bool
     */
    public function isActivationMenu(): bool;

    /**
     * Get activation menu
     * @return array|null
     */
    public function getActivationMenu(): ?array;

    /**
     * activates bonus with a menu option
     * @param $option_value
     * @return bool bonus is activated
     */
    public function ActivateByMenuOption($option_value): bool;

    /**
     * called when user deposits money
     * for the bonuses with status STATUS_WAITING_FOR_PAYMENT
     * @param MUser $user
     * @param float $amount deposit money sum
     * @param int $currency deposit currency code
     * @return float rest of the money (if exists)
     *              that will be payed to users account
     */
    public function confirmPayment(MUser $user, float $amount, int $currency): float;

    /**
     * called when a user deposits money
     * for the bonuses with status STATUS_DEPOSIT_LISTEN
     * @param float $amount deposit money sum
     * @param int $currency deposit currency code
     * @return bool success
     */
    public function onDeposit(float $amount, int $currency): bool;

    /**
     * bonus requires call confirmPayment method
     * on money deposit
     * @return bool
     */
    public function shouldProcessPayment(): bool;

    /**
     * called for bonuses with the status WAITING_FOR_LOSE
     * when user balance is 0
     */
    public function onUserLose();

    /**
     * Transfers bonus money to the real money wallet.
     */
    public function transferBonusMoney(): float;

    /**
     * Returns games which are available to play for this bonus.
     * @return GamesCollection
     */
    public function getAllowedGames(): GamesCollection;

    /**
     * Checks if given game is allowed to play.
     * @param Game $game
     * @return bool
     */
    public function isGameAllowed(Game $game): bool;

    /**
     * returns bonus description id string
     * @return string
     */
    public function getDescriptionName(): ?string;

    /**
     * game url to start play the bonus
     * @return string
     */
    public function getPlayUrl(): string;

    /**
     * bonus has only one game
     * and should start it without game choose
     * @return bool
     */
    public function isDirectPlayUrl(): bool;

    /**
     * returns name of the bonus game
     * @return string
     */
    public function getGameName(): string;

    /**
     * player can't choose balance mode
     * bonus balance mode should be set at
     * game start if the game is allowed
     * @return bool
     */
    public function forceToPlay(): bool;

    /**
     * get bonus renderer (views generator)
     * @return BonusRenderer
     */
    public function getRenderer(): BonusRenderer;

//    public function save(): bool;
}
