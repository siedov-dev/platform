<?php

namespace App\Http\Requests;

use App\Helpers\Captcha;
use App\Helpers\Hlog;
use App\Mail\CheckMail\CheckMail;
use App\Mail\MailConfig;
use App\Models\SiteData;
use App\MUser;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::guest();
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

//            if (!Cache::get('recaptcha', false)) {
//                if (!Captcha::verify()) {
//                    $validator->errors()->add('captcha', __('reg.captcha_error'));
//                } else {
//                    Cache::put('recaptcha', true, 120);
//                }
//            }

            if (SiteData::getFromJson('captcha_reg', false) && intval($this->post('captcha', '')) !== intval(session('captcha'))) {
                $validator->errors()->add('captcha', __('reg.captcha_error'));
            }

            $phone = $this->post('phone', '');
            $email = $this->post('email', '');
            $loginType = $this->post('login_type', 'both');
            $phonePrefix = $this->post('phone_prefix', '');

            if (empty($this->post('ageConfirm', ''))) {
                $validator->errors()->add('ageConfirm', __('reg.ageConfirm'));
            }

            if (($loginType === 'email' || $loginType === 'both') && empty($email)) {
                $validator->errors()->add('email', __('reg.email_required'));
            }

            if (($loginType === 'phone' || $loginType === 'both') && empty($phone)) {
                $validator->errors()->add('phone', __('reg.phone_required'));
            }

            $urls = [
                'loginUrl' => route('login', [], false),
                'recoverUrl' => route('forgotPass', [], false)
            ];

            if ($loginType === 'email' || $loginType === 'both' || $loginType === 'ext') {
                if (!preg_match('/^([a-zA-Z0-9_\-.]+)@[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-.]+$/', $email, $match)) {
                    $validator->errors()->add('email', __('reg.invalid_email'));
                } else {
                    $existingUser = MUser::where('email', $email)->first();
                    if ($existingUser !== null) {
                        $validator->errors()->add('email', __('reg.email_already_exists', $urls));
                    }
                }
                //check disposable email
                try {
                    if (MailConfig::checkRegEmail() && !$validator->errors()->has('email')) {
                        $info = CheckMail::email($email);
                        
                        
                        if (isset($info['verdict']) && $info['verdict'] === 'block') {
                            $validator->errors()->add('email', __('reg.invalid_email'));
                        } 
                        
                        elseif (isset($info['status']) && $info['status'] === 'disposable') {
                            $validator->errors()->add('email', __('reg.invalid_email'));
                        }
                    }
                } catch (\Exception $e) {
                    Hlog::logException($e);
                }
            }
            if ($loginType === 'phone' || $loginType === 'both') {
                $phone = $phonePrefix . $phone;
                if (!preg_match('/^\+[0-9]{8,}$/', $phone)) {
                    $validator->errors()->add('phone', __('reg.phone_already_exists', $urls));
                } else {
                    $existingUser = MUser::where('phone', $phone)->first();
                    if ($existingUser !== null) {
                        $validator->errors()->add('phone', __('reg.phone_already_exists', $urls));
                    }
                }
            }

            if ($loginType === 'ext') {
                if (!preg_match('#^\S+$#', $this->post('name', ''))) {
                    $validator->errors()->add('name', __('reg.name_required'));
                }
                if (!preg_match('#^\S+$#', $this->post('surname', ''))) {
                    $validator->errors()->add('surname', __('reg.surname_required'));
                }
//                if (empty($this->post('address', ''))) {
//                    $validator->errors()->add('address', __('reg.address_required'));
//                }
                if (empty($this->post('city', ''))) {
                    $validator->errors()->add('city', __('reg.city_required'));
                }
                if (empty($this->post('country', ''))) {
                    $validator->errors()->add('country', __('reg.country_required'));
                }
//                if (empty($this->post('postcode', ''))) {
//                    $validator->errors()->add('postcode', __('reg.postcode_required'));
//                }
                if (!preg_match('#^[a-zA-Z0-9\s]+$#', $this->post('state', ''))) {
                    $validator->errors()->add('state', __('reg.state_required'));
                }
                if (empty($this->post('birthday', ''))) {
                    $validator->errors()->add('birthday', __('reg.birthday_required'));
                } else {
                    $birthday = Carbon::parse($this->post('birthday'));
                    if ($birthday === null || $birthday->diffInYears(now()) < 18) {
                        $validator->errors()->add('birthday', __('reg.must_be_18'));
                    }
                }
            }
        });
    }

    public function getLogin()
    {
        $phone = $this->post('phone_prefix', '') . $this->post('phone', '');
        $email = $this->post('email', '');

        if (!empty($email)) {
            return explode('@', $email)[0];
        }
        return $phone;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'password' => 'required|min:6',
            'currency' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'currency.required' => __('reg.currency_required'),
            'password.required' => __('reg.password_required'),
            'password.min' => __('reg.password_min'),
        ];
    }
}
