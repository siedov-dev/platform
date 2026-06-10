<?php

namespace App\Jobs;

use App\Helpers\GoldPartnersSdk;
use App\Models\AdBonusToken;
use App\Models\Partner;
use App\MUser;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @deprecated
 */
class CreateJoinBonusByTokenJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private int $user_id;
    private string $token;
    private ?int $click_id;
    private ?string $publisher_id;

    /**
     * Create a new job instance.
     * @param int $user_id
     * @param string $token
     * @param int|null $click_id
     * @return void
     */
    public function __construct(int $user_id, string $token, ?int $click_id = null, ?string $publisher_id = '')
    {
        $this->user_id = $user_id;
        $this->token = $token;
        $this->click_id = $click_id;
        $this->publisher_id = $publisher_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /** @var MUser $user */
        $user = MUser::findOrFail($this->user_id);

        $adBonus = AdBonusToken::where('token', $this->token)
            ->where('user_id', null)->first();

        if ($adBonus === null) {
            throw new NotFoundHttpException();
        }

        $partner = Partner::where('id', $adBonus->partner_id)->first();

        if ($partner === null) {
            throw new NotFoundHttpException();
        }

        $clickId = $this->click_id;
        if ($clickId !== null) {
            $user->click_id = $clickId;
        }

        // Notify partners about this event.
        if ($clickId !== null) {
            $sdk = new GoldPartnersSdk(config('goldPartners.casinoId'), config('goldPartners.secretKey'));
            $sdk->clickId = $clickId;
            $sdk->clientId = $user->id;
            $sdk->clientName = $user->login;
            $msg = $sdk->createMessage(GoldPartnersSdk::EVENT_REGISTRATION);
            $sdk->send($msg);
        }

        // Notify c3pa partners about registration.
        if ($user->pid == 125) {
            $publisher_id = $this->publisher_id;
            file_get_contents('http://offers.c3pa.net/postback?clickid=' . $user->id . '&status=2&sum=0' . (empty($publisher_id) ? '' : '&publisher_id=' . $publisher_id));
        }

        // Make link permanent for some partners.
        $excludedTokens = [
            'MFgGuPnMZr7hEdks',
            'JHzPMrNXMEOsyIuE',
            'ZgBXF0t7ZWRda5yd',
        ];
        if (!in_array($adBonus->token, $excludedTokens)) {
            $adBonus->user_id = $user->id;
            $adBonus->save();
        }

        switch ($adBonus->win) {
//            case 2000:
//                $bonus = NonDepositBonus::createForUser($user, 2000);
//                break;
//
//            case '300 wager 20':
//                $bonus = DepositWagerBonus::createForUser($user, 20, 300);
//                break;
//
//            case '100 wager 10':
//                $bonus = DepositWagerBonus::createForUser($user, 10, 100);
//                break;
//
//            case '150 spins':
//                $bonus = SuperFreeSpinsBonus::createForUser($user, 150);
//                break;
//
//            case '100 spins':
//                $bonus = SuperFreeSpinsBonus::createForUser($user, 100);
//                break;
//            case 1500:
//                $bonus = NonDepositBonus::createForUser($user, 1500);
//                break;
            default:
                Log::info('Can`t create bonus with code ' . $adBonus->win);
                break;
        }
    }
}
