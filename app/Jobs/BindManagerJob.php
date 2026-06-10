<?php

namespace App\Jobs;

use App\Helpers\GoldPartnersSdk;
use App\Models\Crm\TelegramClient;
use App\MUser;
use App\UserWallet;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BindManagerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private int $user_id;
    private string $hash;
    private bool $send_reg = false;
    private int $client_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $user_id, string $hash, bool $send_reg = true, int $client_id = 0)
    {
        //
        $this->user_id = $user_id;
        $this->hash = $hash;
        $this->send_reg = $send_reg;
        // telegram client id
        $this->client_id = $client_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::channel('login')->info('bind manager with hash '.$this->hash.' to user '.$this->user_id);
        //
        /** @var TelegramClient $client */
        if (!empty($this->hash)) {
            $client = TelegramClient::where('hash', $this->hash)->first();
        } else {
            $client = TelegramClient::where('id', $this->client_id)->first();
        }

        Log::channel('login')->info('clientId '.$client?->id);
        Log::channel('login')->info('client muserid '.$client?->m_user_id);

        if ($client !== null && $client->m_user_id === null) {
            $client->m_user_id = $this->user_id;
            $client->save();
            Log::channel('login')->info('aff_link_id '.$client->aff_link_id);
            try {
                if (!empty($client->aff_link_id)) {
                    Log::channel('login')->info('partner link found, binding '.$client->aff_link_id);
                    /** @var MUser $user */
                    $user = MUser::find($this->user_id);
                    if (empty($user->click_id)) {
                        $clickId = GoldPartnersSdk::getClickId($client->aff_link_id);
                        Log::channel('login')->info('click_id  '.$clickId);
                        if (!empty($clickId) && $clickId > 0) {
                            $clickId = $user->setClickId($clickId);
                            $user->save();
                            if ($this->send_reg) {
                                $sdk = new GoldPartnersSdk(config('goldPartners.casinoId'), config('goldPartners.secretKey'));
                                $sdk->clickId = $clickId;
                                $sdk->clientId = $user->id;
                                $sdk->clientName = $user->login;
                                $sdk->currency = UserWallet::currencyName($user->currency);
                                $msg = $sdk->createMessage(GoldPartnersSdk::EVENT_REGISTRATION);
                                $sdk->send($msg);
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::channel('login')->error($e->getMessage());
                Log::channel('login')->error($e->getLine());
                Log::channel('login')->error($e->getFile());
                Log::channel('login')->error($e->getTraceAsString());
            }
        }
    }
}
