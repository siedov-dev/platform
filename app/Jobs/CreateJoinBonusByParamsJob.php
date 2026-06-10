<?php

namespace App\Jobs;

use App\Helpers\Localization;
use App\Models\Bonuses\BonusFactory;
use App\Models\Bonuses\KUserBonus;
use App\MUser;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class CreateJoinBonusByParamsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private int $user_id;
    private int $bonus_code;
    private ?int $click_id;
    private bool $onlyBonus;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user_id, $bonus_code, $click_id = null, $onlyBonus = false)
    {
        //
        $this->user_id = $user_id;
        $this->bonus_code = $bonus_code;
        $this->click_id = $click_id;
        $this->onlyBonus = $onlyBonus;
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
        Localization::setAppLocale($user->locale);
        $type = 0;
        $params = '';
        $games = '';

        switch ($this->bonus_code) {

            case 75:
                $type = KUserBonus::TYPE_SLEG_FS;
                $params = '{"freespins":"75","wager":"35","min_sum":"3000","max_sum":"15000","max_win":"0","max_x_win":"2","params_RUB":{"freespins":"75","wager":"35","min_sum":"500","max_sum":"5000","max_win":"0","max_x_win":"2"},"params_UAH":{"freespins":"75","wager":"35","min_sum":"300","max_sum":"3000","max_win":"0","max_x_win":"2"},"params_INR":{"freespins":"75","wager":"35","min_sum":"500","max_sum":"5000","max_win":"0","max_x_win":"2"},"params_USDT":{"freespins":"75","wager":"35","min_sum":"10","max_sum":"50","max_win":"0","max_x_win":"2"},"params_KZT":{"freespins":"75","wager":"35","min_sum":"3000","max_sum":"15000","max_win":"0","max_x_win":"2"}}';
                $games = '{"sleg":["17677c81042fc0beb8dbf6079258388a6f27b39c"]}';
                break;


            case 50000:
                break;


            case 1002:
                $type = KUserBonus::TYPE_SLEG_FS;
                $params = '{"freespins":"100","wager":"35","min_sum":"3000","max_sum":"15000","max_win":"0","max_x_win":"2","params_RUB":{"freespins":"100","wager":"35","min_sum":"500","max_sum":"5000","max_win":"0","max_x_win":"2"},"params_UAH":{"freespins":"100","wager":"35","min_sum":"300","max_sum":"3000","max_win":"0","max_x_win":"2"},"params_INR":{"freespins":"100","wager":"35","min_sum":"500","max_sum":"5000","max_win":"0","max_x_win":"2"},"params_USDT":{"freespins":"100","wager":"35","min_sum":"10","max_sum":"50","max_win":"0","max_x_win":"2"},"params_KZT":{"freespins":"100","wager":"35","min_sum":"3000","max_sum":"15000","max_win":"0","max_x_win":"2"}}';
                $games = '{"sleg":["17677c81042fc0beb8dbf6079258388a6f27b39c"]}';
                break;


            case 10050:
                break;


            case 125:
                $type = KUserBonus::TYPE_SLEG_FS;
                $params = '{"freespins":"125","wager":"35","min_sum":"3000","max_sum":"15000","max_win":"0","max_x_win":"2","params_RUB":{"freespins":"125","wager":"35","min_sum":"500","max_sum":"5000","max_win":"0","max_x_win":"2"},"params_UAH":{"freespins":"125","wager":"35","min_sum":"300","max_sum":"3000","max_win":"0","max_x_win":"2"},"params_INR":{"freespins":"125","wager":"35","min_sum":"500","max_sum":"5000","max_win":"0","max_x_win":"2"},"params_USDT":{"freespins":"125","wager":"35","min_sum":"10","max_sum":"50","max_win":"0","max_x_win":"2"},"params_KZT":{"freespins":"125","wager":"35","min_sum":"3000","max_sum":"15000","max_win":"0","max_x_win":"2"}}';
                $games = '{"sleg":["17677c81042fc0beb8dbf6079258388a6f27b39c"]}';
                break;

            case 200000:
                break;

            case 20050:
                break;

            case 50:
                $type = KUserBonus::TYPE_SLEG_FS;
                $params = '{"freespins":"50","wager":"35","min_sum":"3000","max_sum":"15000","max_win":"0","max_x_win":"2","params_RUB":{"freespins":"50","wager":"35","min_sum":"500","max_sum":"5000","max_win":"0","max_x_win":"2"},"params_UAH":{"freespins":"50","wager":"35","min_sum":"300","max_sum":"3000","max_win":"0","max_x_win":"2"},"params_INR":{"freespins":"50","wager":"35","min_sum":"500","max_sum":"5000","max_win":"0","max_x_win":"2"},"params_USDT":{"freespins":"50","wager":"35","min_sum":"10","max_sum":"50","max_win":"0","max_x_win":"2"},"params_KZT":{"freespins":"50","wager":"35","min_sum":"3000","max_sum":"15000","max_win":"0","max_x_win":"2"}}';
                $games = '{"sleg":["17677c81042fc0beb8dbf6079258388a6f27b39c"]}';
                break;

            default:
                Log::info('Can`t create bonus with code ' . $this->bonus_code);
                break;
        }

        if ($type > 0) {
            $bonus = BonusFactory::createFromParams(
                bonus_type: $type,
                user_id: $user->getId(),
                params: $params,
                currency: $user->getCurrency(),
                allowed_games: $games,
            );
//            $bonus = new UserBonus();
//            $bonus->type = $type;
//            $bonus = UserBonus::convertByType($bonus);
//            $bonus = $bonus->createFromParams($params, $user->id);
//            $bonus->setAllowedGames($games);
//            $bonus->save();
        }

        if ($this->onlyBonus) return;

//        // Notify partners about this event.
//        if ($this->click_id !== null) {
//            $sdk = new GoldPartnersSdk(config('goldPartners.casinoId'), config('goldPartners.secretKey'));
//            $sdk->clickId = $this->click_id;
//            $sdk->clientId = $user->id;
//            $sdk->clientName = $user->login;
//            $sdk->currency = UserWallet::currencyName($user->currency);
//            $msg = $sdk->createMessage(GoldPartnersSdk::EVENT_REGISTRATION);
//            $sdk->send($msg);
//        }


//
//        try {
//            $this->sendEmailVerificationMail($user);
//        } catch (\Exception $e) {
//
//        }
//
//        $letter = new \App\Email([
//            'to' => $user->email,
//            'subject' => 'Krakencassino password',
//            'text' => "Your password in Krakencassino:" . $user->view_password,
//            'html' => \App\Email::createKrakenMailHtml("Your password in Krakencassino Krakencassino:" . $user->view_password, 'Krakencassino'),
//        ]);
//
//        $letter->send(false, true);
    }

//    private function sendEmailVerificationMail(MUser $user)
//    {
//        $token = str_pad(random_int(0, 99999999), 8, '0');
//        $user->email_verification_token = $token;
//        @Mail::to($user->email)->send(new EmailVerification($user));
//
//        DB::table('all_email_history')
//            ->insert([
//                'total' => 1,
//                'sent' => 1,
//                'not_sent' => 0,
//                'title' => 'KrakenCasino - Подтверждение email',
//                'text' => $token,
//                'html' => $user->login,
//                'sender' => 'info@krakencassino.com',
//                'created_at' => now()->format('Y-m-d H:i:s')
//            ]);
//        return true;
//    }
}
