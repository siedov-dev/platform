<?php

namespace App\Console\Commands;

use App\CustomerIo\CioEventType;
use App\CustomerIo\CustomerIo;
use App\Helpers\Hlog;
use App\Mail\MailSystemType;
use App\Mail\ResetPassword;
use App\Models\MessageBlank;
use App\MUser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class RecoveryMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:sendrecovery {user_id} {domain?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'mail:sendrecovery {user_id : The ID of the user} {domain? recovery domain (https://krakencassino.net)}';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Log::info('sending recovery mail to user#'.$this->argument('user_id'));

        /** @var MUser $user */
        $user = MUser::findOrFail($this->argument('user_id'));
        $domain = $this->argument('domain');
        //
        $token = Str::random(32);
        try {
            /** @var MessageBlank $blank */
            $blank = MessageBlank::where('type', MessageBlank::TYPE_PASSWORD)->where('name', 'reset_password')->first();
            try {
                CustomerIo::getInstance()->sendEvent($user->getId(), CioEventType::PasswordReset, [
                    'password_reset_link' => url('/set-new-password?token='.$token, [], true),
                    'channel' => 'email',
                ]);
            } catch (\Throwable $e) {
                Hlog::logException($e, 'mail');
            }
            if ($blank !== null) {
                $blank->sendMail([$user->id], ['token' => $token, 'domain' => $domain], mail_system: MailSystemType::CustomerIo->value, force: true, now: true);
            } else {
                Mail::to($user)->send(new ResetPassword($token));
            }
            MUser::where('id', '=', $user->id)->update(['password_reset_token' => $token]);
        } catch (\Throwable $e) {
            Hlog::logException($e, 'mail');
            return 0;
        }
        return 1;
    }
}
