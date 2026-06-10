<?php

namespace App\Jobs;

use App\Helpers\Hlog;
use App\Models\Bonuses\UserBonus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * @deprecated
 */
class ActualizeBonusJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private int $bonus_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $bonus_id)
    {
        $this->bonus_id = $bonus_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            Log::info('job actualizing bonus#'.$this->bonus_id);
            $bonus = UserBonus::find($this->bonus_id);
            if ($bonus !== null) {
                $bonus = UserBonus::convertByType($bonus);
                $bonus->actualize();
                $bonus->save();
            }
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
    }

    /**
     * Количество секунд, по истечении которых уникальная блокировка задания будет снята.
     *
     * @var int
     */
    public $uniqueFor = 10;

    public function uniqueId(): string
    {
        return $this->bonus_id;
    }
}
