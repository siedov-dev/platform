<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CurrencyResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'symbol' => $this->symbol,
            'code' => $this->code,
            'isCrypto' => (bool) $this->is_crypto,
            'minPayinSum' => $this->min_payin_sum,
            'minPayoutSum' => $this->min_payout_sum,
            'maxPayoutSum' => $this->max_payout_sum,
        ];
    }
}
