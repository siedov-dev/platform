<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Email отписавшиеся от рассылок
 * @property int $id
 * @property string $email
 * @property string $sender
 */
class UnsubscribedEmail extends Model
{
    //
    protected $connection = "mail";
    protected $table = "unsubscribed_emails";
}
