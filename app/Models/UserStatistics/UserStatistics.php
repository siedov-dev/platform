<?php

namespace App\Models\UserStatistics;

use App\Models\UserStatistics\DataProviders\StatCrashBetWinsData;
use App\Models\UserStatistics\DataProviders\StatDataFactory;
use App\Models\UserStatistics\DataProviders\StatDataProvider;
use App\MUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $key
 * @property ?float $dec_val
 * @property ?float $float_val
 * @property ?int $int_val
 * @property ?string $string_val
 */
class UserStatistics extends Model
{
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

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $key): void
    {
        $this->key = $key;
    }

    public function getDecVal(): ?float
    {
        return $this->dec_val;
    }

    public function setDecVal(?float $dec_val): void
    {
        $this->dec_val = $dec_val;
    }

    public function getFloatVal(): ?float
    {
        return $this->float_val;
    }

    public function setFloatVal(?float $float_val): void
    {
        $this->float_val = $float_val;
    }

    public function getIntVal(): ?int
    {
        return $this->int_val;
    }

    public function setIntVal(?int $int_val): void
    {
        $this->int_val = $int_val;
    }

    public function getStringVal(): ?string
    {
        return $this->string_val;
    }

    public function setStringVal(?string $string_val): void
    {
        $this->string_val = $string_val;
    }

    public function getValueByType(StatisticsType $type)
    {
        return match ($type->getDataType()) {
            "int" => $this->getIntVal(),
            "dec" => $this->getDecVal(),
            "float" => $this->getFloatVal(),
            "string" => $this->getStringVal(),
        };
    }

    public function setValueByType(StatisticsType $type, mixed $value): void
    {
        match ($type->getDataType()) {
            "int" => $this->setIntVal($value),
            "dec" => $this->setDecVal($value),
            "float" => $this->setFloatVal($value),
            "string" => $this->setStringVal($value),
        };

        $this->save();
    }

    public static function getValue(MUser $user, StatisticsType $type): mixed
    {
        /** @var UserStatistics $stat */
        $stat = UserStatistics::where('user_id', $user->getId())
            ->where('key', $type->value)
            ->first();

        if ($stat === null) return self::updateStats($user, $type);

        return $stat->getValueByType($type);
    }

    public static function updateStats(MUser $user, StatisticsType $type): mixed
    {
        $data_provider = StatDataFactory::getDataProvider($type);
        $stat = UserStatistics::where('user_id', $user->getId())
            ->where('key', $type->value)
            ->first();
        if ($stat === null) {
            $stat = new UserStatistics();
            $stat->setUserId($user->getId());
            $stat->setKey($type->value);
        }

        $stat->setValueByType($type, $data_provider->getValue($user));
        $stat->save();

        return $stat->getValueByType($type);
    }

}
