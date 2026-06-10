<?php

namespace App\Http\Requests\PaymentCallback;

use Illuminate\Foundation\Http\FormRequest;

use App\PaymentsAPI\SkyPay;

class SkyPayRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return SkyPay::callbackCheckSignature();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            //
        ];
    }
}
