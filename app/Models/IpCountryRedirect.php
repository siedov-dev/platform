<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $country_code
 * @property string $redirect_url
 */
class IpCountryRedirect extends Model
{
    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getCountryCode(): string
    {
        return $this->country_code;
    }

    public function setCountryCode(string $country_code): void
    {
        $this->country_code = $country_code;
    }

    public function getRedirectUrl(): string
    {
        return $this->redirect_url;
    }

    public function setRedirectUrl(string $redirect_url): void
    {
        $this->redirect_url = $redirect_url;
    }
}
