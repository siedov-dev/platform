<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->getId(),
            'login' => $this->getLogin(),
            'email' => $this->email,
            'phone' => $this->phone,
            'currency' => $this->getCurrency(),
            'currencyName' => \App\Wallet::currencyName($this->getCurrency()),
            'balanceMode' => $this->getBalanceMode(),
            'locale' => $this->locale,
            'rank' => $this->rank,
            'emailVerified' => $this->isEmailVerified(),
            'phoneVerified' => $this->isPhoneVerified(true),
            'name' => $this->name,
            'surname' => $this->surname,
        ];
    }
}
