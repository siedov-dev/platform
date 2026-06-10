<?php

namespace App\Models\Popups;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $category
 */
class PopupUnsubscribe extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'category'];
    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public static function isUserUnsubscribed(int $user_id, string $category): bool
    {
        return self::where('user_id', $user_id)->where('category', $category)->first() !== null;
    }

}
