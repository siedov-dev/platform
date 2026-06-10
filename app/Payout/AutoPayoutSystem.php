<?php


namespace App\Payout;


use App\ApplicationForPayment;
use App\Models\PaymentSystems;

interface AutoPayoutSystem
{

    public function checkParams(array $params): bool;

    public function processAutoPayment(ApplicationForPayment $applicationForPayment);

    public function getError(): ?string;

    public function getFormRequisites(string $method, ?PaymentSystems $pay_method): string;

    public function getRequestRequisites(string $method, array $data): array;
}
