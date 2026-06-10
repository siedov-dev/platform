<?php

namespace App\Models\Tournaments\Custom;

use App\GamesProviders\GamesCollection;
use App\Models\Tournaments\TournamentInterface;
use App\Models\Tournaments\TournamentPromo\TournamentPromo;
use Illuminate\Support\Carbon;

abstract class CustomTournament implements TournamentInterface
{
    private static array $_tournaments;
    private static array $active_tournaments;
    private static array $completed_tournaments;

    /**
     * get custom tournaments
     * @return array<CustomTournament>
     */
    private static function getTournaments(): array
    {
        $data =  [
            new NonStopJuly,
        ];
        foreach ($data as $t) {
            self::$_tournaments[$t->getId()] = $t;
        }
        return self::$_tournaments;
    }

    /**
     * active/pending tournaments
     * @return TournamentInterface[]
     */
    public final static function getAvailableTournaments(): array
    {
        if (empty(self::$active_tournaments)) {

            self::$active_tournaments = TournamentPromo::getAvailableTournaments()->all();

            foreach (self::getTournaments() as $t) {
                if ($t->getEndDateTime() >= now()->format('Y-m-d H:i:s'))
                    self::$active_tournaments[$t->getId()] = $t;
            }
        }

        return self::$active_tournaments;
    }

    /**
     * completed tournaments
     * @return TournamentInterface[]
     */
    public static function getCompletedTournaments(): array
    {
        if (empty(self::$completed_tournaments)) {

            self::$completed_tournaments = TournamentPromo::getCompletedTournaments()->all();

            foreach (self::getTournaments() as $t) {
                if ($t->getEndDateTime() < now()->format('Y-m-d H:i:s'))
                    self::$completed_tournaments[$t->getId()] = $t;
            }
        }

        return self::$completed_tournaments;

    }

    public final static function getById(string $id): ?CustomTournament
    {
        $t = self::getTournaments();
        return array_key_exists($id, $t) ? $t[$id] : null;
    }

    abstract public function getId(): string;

    abstract public function getImgUrl(): string;

    abstract public function getBannerUrl(): string;

    abstract public function getName(): string;

    abstract public function getDescription(): string;

    abstract public function getPrizeFund(): string;

    abstract public function getStartDateTime(): string;

    abstract public function getEndDateTime(): string;

    abstract public function getAllowedGames(): GamesCollection;

    abstract public function isActive(): bool;

    public function isPendingStart(): bool
    {
        return false;
    }

    /**
     * start date in localized format d M H:i
     * @return string
     */
    public function getStartTime()
    {
        return Carbon::parse($this->getStartDateTime())->formatLocalized('%d %b %H:%M:');
    }

    /**
     * end date in localized format d M H:i
     * @return string
     */
    public function getEndTime()
    {
        return Carbon::parse($this->getEndDateTime())->formatLocalized('%d %b %H:%M');
    }

    public function cabinetBannerUrl(): string
    {
        return $this->getImgUrl();
    }

    public function infoUrl(): string
    {
        return route('tournaments.info', ['tournament' => $this->getId()], false);
    }

    public function getStartDate(): string
    {
        return now()->parse($this->getStartDateTime())->format('Y-m-d');
    }

    public function getEndDate(): string
    {
        return now()->parse($this->getEndDateTime())->format('Y-m-d');
    }

    public function isTestMode(): bool
    {
        return false;
    }
}
