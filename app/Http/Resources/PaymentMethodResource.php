<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentMethodResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->getId(),
            'name' => $this->name,
            'systemName' => $this->system_name,
            'methodAlias' => $this->method_alias,
            'currencyId' => $this->currency_id,
            'type' => $this->type,
            'imageUrl' => $this->image_url,
            'minSum' => $this->getMinSum(),
            'maxSum' => $this->getMaxSum(),
            'group' => $this->getSystemGroup()->value,
            'emailRequired' => $this->isEmailReq(),
            'phoneRequired' => $this->isPhoneReq(),
        ];
    }
}
