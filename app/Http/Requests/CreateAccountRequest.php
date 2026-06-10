<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 * @deprecated
 */
class CreateAccountRequest extends FormRequest
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'password' => 'required|min:6|confirmed',
            'currency' => 'required'
        ];
    }




    public function messages()
    {
        return [
            'email:unique' => 'Пользователь с таким email уже зарегистрирован',
            'email:email' => 'Введите правильный email',
            'password:min' => 'Пароль должен содержать минимум 6 символов',
            'password:confirmed' => 'Пароли не совпадают',
        ];
    }
}
