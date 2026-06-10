<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PersonalDataResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'name' => $this->name,
            'surname' => $this->surname,
            'patronymic' => $this->patr,
            'birthday' => $this->birthday,
            'country' => $this->country,
            'region' => $this->region,
            'city' => $this->city,
            'address' => $this->address,
            'status' => $this->status,
            'confirmed' => $this->isConfirmed(),
        ];
    }
}
