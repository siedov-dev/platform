<?php

namespace App\Models\Slc;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $ball_type
 * @property boolean $success
 * @property boolean $win
 * @property int $prc_won
 */
class SlcBarrelLog extends Model
{
    use HasFactory;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): void
    {
        $this->user_id = $user_id;
    }

    public function getBallType(): string
    {
        return $this->ball_type;
    }

    public function setBallType(string $ball_type): void
    {
        $this->ball_type = $ball_type;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function setSuccess(bool $success): void
    {
        $this->success = $success;
    }

    public function isWin(): bool
    {
        return $this->win;
    }

    public function setWin(bool $win): void
    {
        $this->win = $win;
    }

    public function getPrc(): int
    {
        return $this->prc_won;
    }

    public function setPrc(int $prc): void
    {
        $this->prc_won = $prc;
    }
}
