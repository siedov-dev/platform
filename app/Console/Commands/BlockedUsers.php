<?php

namespace App\Console\Commands;

use App\Helpers\Localization;
use App\Models\MessageBlank;
use App\MUser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class BlockedUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'musers:check-blocked';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check blocked users to be unblocked';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $locale = App::getLocale();
        $users = MUser::where('blocked', true)
            ->whereNotNull('last_active_user')
            ->where('last_active_user', '<', now()->format('Y-m-d H:i:s'))
            ->get();

        /** @var MUser $user */
        foreach ($users as $user) {
            try {
                $user->blocked = false;
                $user->last_active_user = null;
                $user->save();
                /** @var MessageBlank $blank */
                $blank = MessageBlank::where('name', 'unblock_message')->first();
                if ($blank !== null) {
                    Localization::setAppLocale($user->getLocale());
                    $blank->sendAll([$user->getId()]);
                }
            } catch (\Exception $e) {
                Log::error($e->getMessage());
                Log::error($e->getLine());
                Log::error($e->getFile());
                Log::error($e->getTraceAsString());
            }
        }
        Localization::setAppLocale($locale);
        return Command::SUCCESS;
    }
}
