<?php

namespace App\Console\Commands;

use App\Models\Bonuses\Types\BonusMarker;
use App\Models\Bonuses\UserBonus;
use App\Models\MessageBlank;
use App\MUser;
use App\PayoutTransaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendReminderTgBonus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'retention:tg-bonus';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send telegram bonus remind letters';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Log::channel('retention')->info('Письма напоминалки telegram bonus start');

        MUser::selectRaw('m_users.id, coalesce(users_bonuses.tg_bonus_id, 0) as tg_bonus_id')
            ->leftJoin(DB::raw("(select id as tg_bonus_id, user_id
                                        from users_bonuses
                                        where marker = " . BonusMarker::TelegramBonus->value . "
                                        ) as users_bonuses"), function ($join) {
                $join->on("users_bonuses.user_id", '=', 'm_users.id');
            })
            ->having('tg_bonus_id', 0)
            ->chunk(50, function ($users) {
                $ids = $users->pluck('id')->toArray();
                if (!empty($ids)) {
                    /** @var MessageBlank $blank */
                    $blank = MessageBlank::where('name', 'r1-3')->first();
                    $blank->sendMail($ids);
                }

            });

        Log::channel('retention')->info('Письма напоминалки telegram bonus end');

        return Command::SUCCESS;
    }
}
