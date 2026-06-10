<?php

namespace App\Models\Tournaments;

use App\GamesProviders\GamesCollection;

interface TournamentInterface
{

    /**
     * tournament tile img url
     * @return string
     */
    public function getImgUrl(): string;

    /**
     * tournament description top banner img url
     * @return string
     */
    public function getBannerUrl(): string;

    /**
     * tournament cabinet banner img url
     * @return string
     */
    public function cabinetBannerUrl(): string;

    /**
     * tournament name
     * @return string
     */
    public function getName(): string;

    /**
     * prize fung amount string including currency symbol
     * @return string
     */
    public function getPrizeFund(): string;

    /**
     * start date and time
     * @return string
     */
    public function getStartDateTime(): string;

    /**
     * end date and time
     * @return string
     */
    public function getEndDateTime(): string;

    /**
     * tournament's game collection
     * @return GamesCollection
     */
    public function getAllowedGames(): GamesCollection;

    /**
     * is tournament active at the moment
     * @return bool
     */
    public function isActive(): bool;

    /**
     * is tournament haven't started at the moment
     * @return bool
     */
    public function isPendingStart(): bool;

    /**
     * tournament description page relative url
     * @return string
     */
    public function infoUrl(): string;

    /**
     * start date
     * @return string
     */
    public function getStartDate(): string;

    /**
     * end date
     * @return string
     */
    public function getEndDate(): string;

    public function isTestMode(): bool;
}
