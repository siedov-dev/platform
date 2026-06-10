<?php

namespace App\Models;

use App\MUser;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PersonalData
 * Персональные данные пользователей.
 *
 * @property int id
 * @property int user_id
 * @property string name
 * @property string surname
 * @property string birthday
 * @property string filename
 * @property int status
 * @property string patr
 * @property string passport
 * @property string country
 * @property string region
 * @property string city
 * @property string address
 * @property string $message
 */
class PersonalData extends Model
{
    protected $hidden = [
        'passport',
        'filename',
    ];

    const STATUS_REQUIRED = -1;
    const STATUS_NOT_CONFIRMED = 0;
    const STATUS_CONFIRMED = 1;
    const STATUS_REQUESTED = 2;
    const STATUS_DECLINED = 3;
    const STATUS_CHANGED = 4;

    public function user()
    {
        return $this->belongsTo('App\MUser', 'user_id', 'id');
    }

    /**
     * user's data verified
     * @return bool
     */
    public function isConfirmed(): bool
    {
        return in_array($this->status, [self::STATUS_CONFIRMED]);
    }

    /**
     * user sent data to verify
     * @return bool
     */
    public function isRequested(): bool
    {
        return in_array($this->status, [self::STATUS_REQUESTED, self::STATUS_CHANGED]);
    }

    /**
     * user's data not verified
     * @return bool
     */
    public function isNotConfirmed(): bool
    {
        return in_array($this->status, [self::STATUS_DECLINED, self::STATUS_NOT_CONFIRMED, self::STATUS_REQUIRED]);
    }

    /**
     * user's data required
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->status == self::STATUS_REQUIRED;
    }

    public function getStatusString(): string
    {
        return match ($this->status) {
            self::STATUS_REQUIRED => __('verification.status_required'),
            self::STATUS_CONFIRMED => __('verification.status_confirmed'),
            self::STATUS_REQUESTED => __('verification.status_requested'),
            self::STATUS_DECLINED => __('verification.status_declined'),
            self::STATUS_CHANGED => __('verification.status_changed'),
            self::STATUS_NOT_CONFIRMED => __('verification.status_not_confirmed'),
            default => "",
        };
    }

    public function getStatus(): int
    {
        return $this->status ?? 0;
    }

    public function getMessage(): string
    {
        return $this->message ?? '';
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getSurname(): string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): void
    {
        $this->surname = $surname;
    }

    public function getBirthday(): string
    {
        return $this->birthday;
    }

    public function setBirthday(string $birthday): void
    {
        $this->birthday = $birthday;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): void
    {
        $this->filename = $filename;
    }

    public function getPatr(): string
    {
        return $this->patr;
    }

    public function setPatr(string $patr): void
    {
        $this->patr = $patr;
    }

    public function getPassport(): string
    {
        return $this->passport;
    }

    public function setPassport(string $passport): void
    {
        $this->passport = $passport;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setCountry(string $country): void
    {
        $this->country = $country;
    }

    public function getRegion(): string
    {
        return $this->region;
    }

    public function setRegion(string $region): void
    {
        $this->region = $region;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): void
    {
        $this->address = $address;
    }
}
