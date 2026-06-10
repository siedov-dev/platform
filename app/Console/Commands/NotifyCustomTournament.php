<?php

namespace App\Console\Commands;

use App\Helpers\Localization;
use App\Models\Crm\TelegramClient;
use App\Models\Popups\UserPopup;
use App\Models\Tournaments\Custom\CustomTournament;
use App\Telegram\Models\TelegramUser;
use App\Telegram\TelegramBot;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class NotifyCustomTournament extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tournament:notify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify users about tournament event';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        set_time_limit( ini_get('max_execution_time') + 1600);
        $customTnts = CustomTournament::getAvailableTournaments();
        /** @var CustomTournament $custom_tnt */
        foreach ($customTnts as $custom_tnt) {
            $startTime = Carbon::createFromFormat('Y-m-d H:i:s', $custom_tnt->getStartDateTime());
            $hours = now()->diffInHours($startTime, false);
            if (abs($hours) > 24) continue;
            /** @var TelegramUser $tgUser */
            foreach (TelegramUser::all() as $tgUser) {
                Localization::setAppLocale($tgUser->getLocale());
                if ($hours > 0) {
                    $msg = __('tournament.tomorrow_start') . $custom_tnt->getName();
                } else {
                    $msg = __('tournament.started') . $custom_tnt->getName();
                }
                TelegramBot::sendChatPhoto(public_path($custom_tnt->getImgUrl()), $msg, $tgUser->getTelegramId());
            }

            /** @var TelegramClient $tg_client */
            foreach (TelegramClient::all() as $tg_client) {
                Localization::setAppLocale($tg_client->getLocale()?? 'en');
                if ($hours > 0) {
                    $msg = __('tournament.tomorrow_start') . $custom_tnt->getName();
                } else {
                    $msg = __('tournament.started') . $custom_tnt->getName();
                }
                TelegramBot::sendChatPhoto(public_path($custom_tnt->getImgUrl()), $msg, $tg_client->getTelegramId(),
                    bot_token: config('manager_bot.token'));
            }

            foreach (Localization::getAvailableLocales() as $locale) {
                Localization::setAppLocale($locale);
                UserPopup::createGlobal(view('common.popups.tournament', [
                    'img' => $custom_tnt->getImgUrl(),
                    'name' => $custom_tnt->getName(),
                    'msg' => $msg,
                ])->render(), 'tournament', 3, now(), now()->endOfDay(), $locale);
            }
        }
        return Command::SUCCESS;
    }
}
