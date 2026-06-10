<?php

namespace App\Models\Bonuses;

use App\MUser;
use App\Services\KBonus\KBonus;
use App\Wallet;

class BonusFactory
{
    /**
     * get user bonuses awaiting deposit action
     * @param MUser $user
     * @return array<KUserBonus>
     */
    public static function getOnDepositBonuses(MUser $user): array
    {
        $bonuses = [];
        $b = KBonus::getInstance()->getUserBonuses($user->getId(), $user->getCurrency());
        foreach ($b as $bonus) {
            if ($bonus->status === KUserBonus::STATUS_DEPOSIT_LISTEN)
            $bonuses[] = $bonus;
        }
        return $bonuses;
    }

    public static function createFromParams(
        int     $bonus_type,
        int     $user_id,
        string  $params,
        ?int    $currency = null,
        ?bool   $keep_params = false,
        ?bool   $tmp = false,
        ?int    $pattern_id = 0,
        ?int    $creator_type = 0,
        ?int    $creator_id = 0,
        ?string $title = '',
        ?string $img_url = '',
        ?int    $marker = 0,
        ?string $allowed_games = "",
        ?int $wager_game_segment_id = null,
    ): ?KUserBonus
    {
        return KBonus::getInstance()->createFromParams(
            bonus_type: $bonus_type,
            user_id: $user_id,
            params: $params,
            currency: $currency,
            keep_params: $keep_params,
            tmp: $tmp,
            pattern_id: $pattern_id,
            creator_type: $creator_type,
            creator_id: $creator_id,
            title: $title,
            img_url: $img_url,
            marker: $marker,
            allowed_games: $allowed_games,
            wager_game_segment_id: $wager_game_segment_id,
        );
    }

    public static function createFromType(array &$attributes, array &$meta): ?KUserBonus
    {
        $type = intval($attributes['type']);
        $bonus = match ($type) {
            KUserBonus::TYPE_NON_DEPOSIT => new NonDepositBonus($attributes, $meta),
//            KUserBonus::TYPE_FREE_SPINS => new FreeSpinsBonus($attributes, $meta),
//            KUserBonus::TYPE_PLUS_MINUS => new PlusMinusBonus($attributes, $meta),
//            KUserBonus::TYPE_FREE_SPINS_WAGER => new WagerFreeSpinsBonus($attributes, $meta),
//            KUserBonus::TYPE_SUPER_FREE_SPINS => new SuperFreeSpinsBonus($attributes, $meta),
//            KUserBonus::TYPE_SUPER_FREE_SPINS_PLAYBACK => new SuperFreeSpinsPlayback($attributes, $meta),
//            KUserBonus::TYPE_EVENT_CASH_BACK => new CashbackEventBonus($attributes, $meta),
//            KUserBonus::TYPE_DEPOSIT_WAGER => new DepositWagerBonus($attributes, $meta),
//            KUserBonus::TYPE_SUPER_FS_CASHBACK => new SuperFsCashbackBonus($attributes, $meta),
//            KUserBonus::TYPE_SUPER_FS_CASHBACK_PB => new SuperFsCashbackPbBonus($attributes, $meta),
//            KUserBonus::TYPE_FILTHY_FS => new FilthyFreeSpinsBonus($attributes, $meta),
//            KUserBonus::TYPE_FILTHY_FS_WASH_OFF => new FilthyFsWashOffBonus($attributes, $meta),
//            KUserBonus::TYPE_BEZDEP => new BezdepBonus($attributes, $meta),
            KUserBonus::TYPE_DEPOSIT_PRIZE => new DepositPrizeBonus($attributes, $meta),
//            KUserBonus::TYPE_DEPOSIT_FFS_TNT => new DepositFFrespinsTntBonus($attributes, $meta),
//            KUserBonus::TYPE_NON_DEPOSIT_WAGER => new NonDepWagerBonus($attributes, $meta),
            KUserBonus::TYPE_CHOOSE_WAGER_FS2 => new ChooseWagerFs2($attributes, $meta),
//            KUserBonus::TYPE_SMART_WAGER => new SmartWagerBonus($attributes, $meta),
//            KUserBonus::TYPE_TOURNAMENT_WIN => new TournamentWinBonus($attributes, $meta),
//            KUserBonus::TYPE_PPLAY_FS => new PPlayFreeSpins($attributes, $meta),
//            KUserBonus::TYPE_PPLAY_SMART_FS => new PPlaySmartFs($attributes, $meta),
//            KUserBonus::TYPE_QT_FS_WAGER => new QtFsWager($attributes, $meta),
//            KUserBonus::TYPE_QT_SMART_FS => new QtSmartFs($attributes, $meta),
//            KUserBonus::TYPE_SLEG_FS => new SlegFsWager($attributes, $meta),
//            KUserBonus::TYPE_SLEG_SMART => new SlegSmartFs($attributes, $meta),
//            KUserBonus::TYPE_PP_NONDEP => new PPNonDepFs($attributes, $meta),
//            KUserBonus::TYPE_SLEG_NONDEP => new SlegNonDepFs($attributes, $meta),
//            KUserBonus::TYPE_PP_FS_MENU => new PPlayFSMenu($attributes, $meta),
//            KUserBonus::TYPE_SLEG_FS_MENU => new SlegFsMenu($attributes, $meta),
            KUserBonus::TYPE_CASHBACK_MONEY => new CashbackMoneyBonus($attributes, $meta),
//            KUserBonus::TYPE_SWISS_FS_WAGER => new SwissFsWager($attributes, $meta),
//            KUserBonus::TYPE_PPLAY_SFS_DYNAMIC => new PPlaySfsDyn($attributes, $meta),
//            KUserBonus::TYPE_SLEG_SFS_DYNAMIC => new SlegSfsDyn($attributes, $meta),
            KUserBonus::TYPE_MULTI_DAYS => new MultiDaysBonus($attributes, $meta),
//            KUserBonus::TYPE_PLUS_MINUS_DEP => new PlusMinusDepBonus($attributes, $meta),
            KUserBonus::TYPE_SHARED_MONEY => new SharedMoneyBonus($attributes, $meta),
            KUserBonus::TYPE_SHARED_MONEY_FS => new SharedMoneyFsBonus($attributes, $meta),
            KUserBonus::TYPE_WELCOME_BONUS_PACK => new WelcomeBonusPack($attributes, $meta),
            KUserBonus::TYPE_PS_FS => new PSFreeSpins($attributes, $meta),
            KUserBonus::TYPE_PS_FS_MENU => new PSFSMenu($attributes, $meta),
            KUserBonus::TYPE_PS_SFS_DYNAMIC => new PSSfsDyn($attributes, $meta),
            KUserBonus::TYPE_PS_SMART => new PSSmartFs($attributes, $meta),
            KUserBonus::TYPE_PS_NON_DEP => new PSNonDepFs($attributes, $meta),
            KUserBonus::TYPE_NON_DEP => new NonDepBonus($attributes, $meta),
            KUserBonus::TYPE_STICKY_FS_MENU => new StickyFSMenu($attributes, $meta),
            default => new UnsupportedBonus($attributes, $meta),
//            default => throw new \Exception('Unknown bonus type'),
        };
        $bonus->curr_name = Wallet::currencyName($attributes['currency'] ?? Wallet::CURRENCY_RUB);
        return $bonus;
    }
}
