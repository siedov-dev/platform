<?php

namespace App\Jobs;

use App\CustomerIo\CioEventType;
use App\CustomerIo\CustomerIo;
use App\Helpers\GoldPartnersSdk;
use App\Helpers\Hlog;
use App\Services\IpInfo\IpInfo;
use App\Services\NatsService\NatsUserEvents;
use App\Services\Users\UserManager;
use App\Mail\MailSystemType;
use App\Mail\Registration;
use App\Models\MessageBlank;
use App\Models\Slc\SlcCompletedTask;
use App\Models\Slc\SlcTaskStatus;
use App\Models\Slc\SlcTaskType;
use App\MUser;
use App\UserWallet;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RegistrationDoneJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private int $user_id;
    private bool $send_click;
    private string $host;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $user_id, bool $send_click = true, $host = "")
    {
        $this->user_id = $user_id;
        $this->send_click = $send_click;
        $this->host = $host;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('check done reg, id#'.$this->user_id);
        if ($this->user_id) {
            Log::info('registration done, user#'.$this->user_id);
            /** @var MUser $user */
            $user = MUser::find($this->user_id);
            if ($user !== null) {
                Log::info('user found, sending background data');
                UserManager::addEvent($this->user_id, 'regfin');

//                if (!empty($user->phone)) {
//                    //create new manager's client
//                    $client = new TelegramClient();
//                    $client->m_user_id = $user->getId();
//                    $client->status = $client::STATUS_REGISTERED;
//                    $client->locale = $user->getLocale();
//                    $client->save();
//                }

                if ($this->send_click) {
                    $clickId = $user->click_id;
                    if ($clickId !== null) {
                        $sdk = new GoldPartnersSdk(config('goldPartners.casinoId'), config('goldPartners.secretKey'));
                        $sdk->clickId = $clickId;
                        $sdk->clientId = $user->id;
                        $sdk->clientName = $user->login;
                        $sdk->currency = UserWallet::currencyName($user->currency);
                        $msg = $sdk->createMessage(GoldPartnersSdk::EVENT_REGISTRATION);
                        $sdk->send($msg);
                    }
                }

//                if ($user->ref_id){
//                    /** @var UserReferral $ref */
//                    $ref = UserReferral::find($user->ref_id);
//                    if ($ref) {
//                        $ref->onRegister($user);
//                    }
//                }

                $confirm_link = "https://" . $this->host . "/verify-email?token=" . $user->email_verification_token;
                if (!empty($user->email)) {
                    try {
                        /** @var MessageBlank $blank */
                        $blank = MessageBlank::where('type', MessageBlank::TYPE_REGISTER)
                            ->where('name', 'registration_mail')
                            ->first();
                        if ($blank !== null) {
                            $blank->sendMail(user_ids: [$user->id], blade_data: ['confirm_link' => $confirm_link],
                                mail_system: MailSystemType::Kraken->value,
                                force: true,
                                now: true);
                        } else {
                            Mail::to($user)->send(new Registration());
                        }
                    } catch (\Throwable $e) {
                        Hlog::logException($e, 'mail');
                    }

//                    try {
//                        /** @var MessageBlank $blank */
//                        $blank = MessageBlank::where('name', 'ret2_wb')->first();
//                        if ($blank !== null) {
//                            $blank->sendMail(user_ids: [$user->id],
//                                blade_data: [],
//                                mail_system: MailSystemType::Kraken->value,
//                                force: true,
//                                now: true);
//                        } else {
//                            Log::channel('retention')->info("Can't get ret2_wb blank");
//                        }
//                    } catch (\Exception $e) {
//                        Log::channel('mail')->error($e->getMessage());
//                    }

                }

                SlcCompletedTask::setTaskStatus($user->getId(), SlcTaskType::Registration, SlcTaskStatus::Completed);

                NatsUserEvents::registered($user->getId(), $user->getCurrency(), $user->ref_id, $user->click_id, $user->getFingerprint());

                $ip_info = IpInfo::getInfo($user->last_ip);
                CustomerIo::getInstance()->sendEvent($user->getId(), CioEventType::Register, [
                    'user_id' => $user->getId(),
                    'currency' => $user->getCurrencyName(),
                    'ref_id' => $user->ref_id,
                    'click_id' => $user->click_id,
                    'fingerprint' => $user->getFingerprint(),
                    'ip' => $user->last_ip,
                    'locale' => $user->getLocale(),
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'country' => $ip_info?->country_code,
                    'language' => $user->getLocale(),
                    'email_verification_link' => $confirm_link,
                ]);
            }
        }
    }
}
