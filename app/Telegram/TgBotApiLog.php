<?php

namespace App\Telegram;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $bot_id
 * @property string $send_params
 * @property string $response
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class TgBotApiLog extends Model
{
    use HasFactory;

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function getId(): int
    {
        return $this->id;
    }

    public function getBotId(): string
    {
        return $this->bot_id;
    }

    public function setBotId(string $bot_id): void
    {
        $this->bot_id = $bot_id;
    }

    public function getSendParams(): string
    {
        return $this->send_params;
    }

    public function setSendParams(string $send_params): void
    {
        $this->send_params = $send_params;
    }

    public function getResponse(): string
    {
        return $this->response;
    }

    public function setResponse(string $response): void
    {
        $this->response = $response;
    }

    public function getCreatedAt(): Carbon
    {
        return $this->created_at;
    }

    public function getUpdatedAt(): Carbon
    {
        return $this->updated_at;
    }

}
