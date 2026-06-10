<?php

namespace App\Mail;

use Illuminate\Database\Eloquent\Model;

/**
 * Class MailConfig
 * @package App\Mail
 * @property string $system
 * @property int $max_mails_at_once
 * @property string $from_email
 * @property string $unisender_key
 * @property string $sendgrid_key
 * @property string $game_domain
 * @property boolean $check_reg_email
 */
class MailConfig extends Model
{
    //
    private static array $_config = [];

    private static function getConfig(): array
    {
        if (empty(self::$_config)) {
            /** @var MailConfig $config */
            $config = MailConfig::first();
            if ($config) self::$_config = $config->toArray();
        }
        return self::$_config;
    }

    public static function getSystem(): string
    {
        return self::getConfig()['system'] ?? 'unisender';
    }

    public static function getMaxMails(): int
    {
        return self::getConfig()['max_mails_at_once']?? 500;
    }

    public static function getFrom(): string
    {
        return self::getConfig()['from_email'] ?? 'info@krakenmail.info';
    }

    public static function getUnisenderKey()
    {
        return self::getConfig()['unisender_key'] ?? '6xhcm9rikbi68gunj1jnbq7diqwr6d3uska5tdzy';
    }

    public static function getSendgridKey()
    {
        return self::getConfig()['sendgrid_key'] ?? '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';
    }

    public static function getGameDomain()
    {
        return self::getConfig()['game_domain'] ?? 'krakencassino.net';
    }

    public static function checkRegEmail(): bool
    {
        return  self::getConfig()['check_reg_email'] ?? false;
    }

    public function setCheckRegEmail(bool $check_reg_email): void
    {
        $this->check_reg_email = $check_reg_email;
    }
}
