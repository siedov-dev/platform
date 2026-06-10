<?php

namespace App\Mail;

use App\Helpers\BladeString;
use App\Helpers\Localization;
use App\MUser;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class MailManager
{
    const MAX_EMAILS_CHUNK = 2000;

    public static function sendToIds(array $ids, string $title, string $content, ?string $locale = null, $mail_system = null): void
    {
        set_time_limit(ini_get('max_execution_time') + 60 * 60 * 4);
        Log::channel('mail')->info(__CLASS__ . __METHOD__ . ' send mail to ids:' . var_export($ids, true));

        $curr_locale = App::getLocale();

        $emails = MUser::whereIn('id', $ids)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->where('locale', $locale)
            ->pluck('email')
            ->toArray();

        foreach ($emails as $addresses) {
            if (empty($addresses)) {
                Log::channel('mail')->info("locale $locale email list is empty");
                continue;
            }
            $letter = new \App\Email([
                'to' => $addresses,
                'subject' => $title,
                'html' => $content,
            ], $mail_system);

            try {
                $letter->send();
            } catch (\Exception $e) {
                Log::channel('mail')->error($e->getMessage());
                Log::channel('mail')->error($e->getFile());
                Log::channel('mail')->error($e->getLine());
                Log::channel('mail')->error($e->getTraceAsString());
            }
        }

        Localization::setAppLocale($curr_locale);
    }

    public static function sendToAll(string $title, string $content, ?string $locale = null, $mail_system = null): void
    {
        set_time_limit(ini_get('max_execution_time') + 60 * 60 * 4);

        MUser::whereNotNull('email')
            ->where('email', '<>', '')
            ->where('locale', $locale)
            ->chunk(self::MAX_EMAILS_CHUNK, function ($users) use ($title, $content, $locale, $mail_system) {
                $ids = $users->pluck('id')->toArray();
                Log::channel('mail')->info(__CLASS__ . __METHOD__ . 'sending chunk mail size ' . count($ids));
                self::sendToIds($ids, $title, $content, $locale, $mail_system);
            });
    }
}
