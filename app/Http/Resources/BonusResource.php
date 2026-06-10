<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BonusResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->getId(),
            'type' => $this->type,
            'status' => $this->status,
            'statusName' => $this->getStatusName(),
            'name' => $this->getName(),
            'description' => $this->getShortDescription(),
            'currency' => $this->getCurrency(),
            'currencyName' => $this->getCurrencyName(),
            'title' => $this->getTitle(),
            'imgUrl' => $this->getImgUrl(),
            'isDeposit' => $this->isDeposit(),
            'isFreeSpins' => $this->isFreeSpins(),
            'spinsTotal' => $this->getSpinsTotal(),
            'spinsAvailable' => $this->getSpinsAvailable(),
            'spinsMade' => $this->getSpinsMade(),
            'moneyAvailable' => $this->getMoneyAvailable(),
            'balanceString' => $this->getBalanceString(),
            'balanceName' => $this->getBalanceName(),
            'progressAvailable' => $this->isProgressAvailable(),
            'progressPercents' => $this->getProgressPercents(),
            'progressHint' => $this->getProgressHint(),
            'targetHint' => $this->getTargetHint(),
            'requiredSum' => $this->getRequiredSum(),
            'isActive' => $this->isActive(),
            'isPending' => $this->isPending(),
            'isCompleted' => $this->isCompleted(),
            'isCancelable' => $this->isCancelable(),
            'paymentRequired' => $this->isPaymentRequired(),
            'agreementRequired' => $this->isAgreementRequired(),
        ];
    }
}
