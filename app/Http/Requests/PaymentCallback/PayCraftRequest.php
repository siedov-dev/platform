<?php

namespace App\Http\Requests\PaymentCallback;

use App\Payin\PayCraftPayin;
use App\PaymentsAPI\PayCraft;
use Illuminate\Foundation\Http\FormRequest;

use App\PaymentsAPI\SkyPay;
use Illuminate\Validation\Rule;

/**
 * @deprecated
 */
class PayCraftRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => [
                'required',
                Rule::in([
                    PayCraftPayin::STATUS_PENDING,
                    PayCraftPayin::STATUS_SUCCESS,
                    PayCraftPayin::STATUS_ERROR
                ])
            ]
        ];
    }
}
