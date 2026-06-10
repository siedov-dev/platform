<?php


namespace App\Telegram\Commands;


use App\Http\Controllers\GetBonusController;
use App\Models\Bonuses\UserBonus;
use App\Models\BonusEvents\BonusEvent;
use App\Models\Tournaments\Custom\CustomTournament;
use App\Models\Tournaments\Tournament;
use App\MUser;
use App\Telegram\Models\TelegramUser;
use App\Telegram\TelegramBot;
use Carbon\Carbon;
use Telegram\Bot\Objects\Message;
use Telegram\Bot\Objects\User;

class CommandEvents implements CommandInterface
{
    private TelegramBot $bot;

    public function __construct(TelegramBot &$bot)
    {
        $this->bot = $bot;
    }

    public function process(string $params, User $user, TelegramUser $t_user, Message $message): ?Message
    {
        $msg = '';

        /** @var Tournament $tnt */
        $tnt = Tournament::getActive();
        if ($tnt) {
            $msg .= __('telegram.active_tnt') . $tnt->getName() . PHP_EOL;
        }

        $customTnts = CustomTournament::getAvailableTournaments();
        /** @var CustomTournament $custom_tnt */
        foreach ($customTnts as $custom_tnt) {
            if ($custom_tnt->getStartDateTime() <= now()->format('Y-m-d H:i:s')) {
                if ($custom_tnt->getEndDateTime() > now()->format('Y-m-d H:i:s')) $msg .= __('telegram.active_tnt') . $custom_tnt->getName() . PHP_EOL;
            }
        }

        foreach ($customTnts as $custom_tnt) {
            if ($custom_tnt->getStartDateTime() > now()->format('Y-m-d H:i:s')) {
                $msg .= Carbon::createFromFormat('Y-m-d H:i:s', $custom_tnt->getStartDateTime())->isoFormat('DD.MM.YYYY').' '. __('telegram.next_tnt') . $custom_tnt->getName() . PHP_EOL;
            }
        }

        if (empty($msg)) {
            $msg = __('telegram.no_tnt_events');
        }

        return $this->bot->getApi()->sendApiMessage([
            'chat_id' => $user->getId(),
            'text' => $msg,
            'reply_markup' => json_encode($t_user->getUserMenu()),
        ]);
    }
}
