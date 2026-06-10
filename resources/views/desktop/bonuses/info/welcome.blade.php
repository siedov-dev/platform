@if(\Illuminate\Support\Facades\Auth::guest())
    @php
        $currency = request()->input('currency', \App\Helpers\Localization::getCurrency());
        $user_currency = \App\UserWallet::currencyName($currency);
    @endphp
    <div class="flex">
        <div class="">
            <label class="input__wrap_modal mt-2">
                <span class="input__label_modal">{{ __('reg.chooseCurrency') }}:</span>
                <div class="img-drop-down drop-down input__wrap_modal reg-form__currency mt-2" id="currency-dropdown">
                    <div class="img-drop-down__current-value" data-value="{{$currency}}"><div class="currency-icon currency-icon_{{mb_strtolower($user_currency)}}"></div>{{$user_currency}}</div>
                    <input type="hidden" class="img-drop-down__selected-value" name="currency" value="{{\App\UserWallet::getCurrencyCode($user_currency)}}">
                    <div class="arrow-down"></div>
                    <div class="img-drop-down__values">
                        @foreach(\App\UserWallet::activeCurrencies() as $currency_code)
                            @php
                                $currency_name = \App\UserWallet::currencyName($currency_code)
                            @endphp
                            <a href="{{request()->url()."?currency=".$currency_code}}" class="img-drop-down__value content-link" data-value="{{$currency_code}}">
                                <div class="currency-icon currency-icon_{{strtolower($currency_name)}}"></div>{{$currency_name}}
                            </a>
                        @endforeach
                    </div>
                </div>
            </label>
        </div>
    </div>
@else
    @php
        $currency = \App\MUser::getAuth()?->getCurrency()?? \App\UserWallet::CURRENCY_USDT;
    @endphp
@endif
@switch($currency)
    @case(\App\UserWallet::CURRENCY_RUB)
        <h1>{{__('bonuses.welcome_ru')}}</h1>
        <p>
            {{__('bonuses.welcome_ru1')}} <br>
            <br><b>{{__('bonuses.welcome_ru2')}}</b><br>
            <b>{{__('bonuses.welcome_ru3')}}</b><br>
            <b>{{__('bonuses.welcome_ru4')}}</b><br>
            <b>{{__('bonuses.welcome_ru4_1')}}</b><br>
            <b>{{__('bonuses.welcome_ru4_2')}}</b><br>
            <b>{{__('bonuses.welcome_ru4_3')}}</b><br>
            <br>
        </p>
        <h5>{{__('bonuses.welcome_ru5')}}</h5>
        <p>{{__('bonuses.welcome_ru6')}} <br>
            <b>1.</b> {{__('bonuses.welcome_ru7')}}<br>
            <b>2.</b> {{__('bonuses.welcome_ru8')}}<b>{{__('bonuses.welcome_ru8_1')}}</b><br>
            <b>3.</b> {{__('bonuses.welcome_ru9')}} <br>
            <b>4.</b> {{__('bonuses.welcome_ru10')}} <br>
            <b>{{__('bonuses.welcome_ru11')}}</b> <br>
            <b>(4.1)</b> {{__('bonuses.welcome_ru12')}}<br>
            <b>(4.2)</b> {{__('bonuses.welcome_ru13')}} <br>
            <b>5.</b> {{__('bonuses.welcome_ru14')}} <br>

        </p>


        <br><h5>{{__('bonuses.welcome_ru34')}}</h5>
        <p> {{__('bonuses.welcome_ru35')}} <br>
            {{__('bonuses.welcome_ru36')}} <b>{{__('bonuses.welcome_ru37')}}</b>)<br>
            <br>1. {{__('bonuses.welcome_ru38')}}<br>
            2. {{__('bonuses.welcome_ru39')}}<br>
            3. {{__('bonuses.welcome_ru40')}}<br>
            4. {{__('bonuses.welcome_ru41')}}<br>
            5. {{__('bonuses.welcome_ru42')}}<br>
        </p>
        @break
    @case(\App\UserWallet::CURRENCY_UAH)
        <h1>{{__('bonuses.welcome_ua')}}</h1>
        <p>
            {{__('bonuses.welcome_ua1')}} <br>
            <br><b>{{__('bonuses.welcome_ua2')}}</b><br>
            <b>{{__('bonuses.welcome_ua3')}}</b><br>
            <b>{{__('bonuses.welcome_ua4')}}</b><br>
            <b>{{__('bonuses.welcome_ua4_1')}}</b><br>
            <b>{{__('bonuses.welcome_ua4_2')}}</b><br>
            <b>{{__('bonuses.welcome_ua4_3')}}</b><br>
            <br>
        </p>
        <h5>{{__('bonuses.welcome_ua5')}}</h5>
        <p>{{__('bonuses.welcome_ua6')}} <br>
            <b>1.</b> {{__('bonuses.welcome_ua7')}}<br>
            <b>2.</b> {{__('bonuses.welcome_ua8')}}<b>{{__('bonuses.welcome_ru8_1')}}</b><br>
            <b>3.</b> {{__('bonuses.welcome_ua9')}} <br>
            <b>4.</b> {{__('bonuses.welcome_ua10')}} <br>
            <b>{{__('bonuses.welcome_ua11')}}</b> <br>
            <b>(4.1)</b> {{__('bonuses.welcome_ua12')}}<br>
            <b>(4.2)</b> {{__('bonuses.welcome_ua13')}} <br>
            <b>5.</b> {{__('bonuses.welcome_ua14')}} <br>

        </p>


        <br><h5>{{__('bonuses.welcome_ua34')}}</h5>
        <p> {{__('bonuses.welcome_ua35')}} <br>
            {{__('bonuses.welcome_ua36')}} <b>{{__('bonuses.welcome_ua37')}}</b>)<br>
            <br>1. {{__('bonuses.welcome_ua38')}}<br>
            2. {{__('bonuses.welcome_ua39')}}<br>
            3. {{__('bonuses.welcome_ua40')}}<br>
            4. {{__('bonuses.welcome_ua41')}}<br>
            5. {{__('bonuses.welcome_ua42')}}<br>
        </p>

        @break
    @case(\App\UserWallet::CURRENCY_EUR)
        <h1>{{__('bonuses.welcome_eur')}}</h1>
        <p>
            {{__('bonuses.welcome_eur1')}} <br>
            <br><b>{{__('bonuses.welcome_eur2')}}</b><br>
            <b>{{__('bonuses.welcome_eur3')}}</b><br>
            <b>{{__('bonuses.welcome_eur4')}}</b><br>
            <b>{{__('bonuses.welcome_eur4_1')}}</b><br>
            <b>{{__('bonuses.welcome_eur4_2')}}</b><br>
            <b>{{__('bonuses.welcome_eur4_3')}}</b><br>
            <br>
        </p>
        <h5>{{__('bonuses.welcome_eur5')}}</h5>
        <p>{{__('bonuses.welcome_eur6')}} <br>
            <b>1.</b> {{__('bonuses.welcome_eur7')}}<br>
            <b>2.</b> {{__('bonuses.welcome_eur8')}}<b>{{__('bonuses.welcome_eur8_1')}}</b><br>
            <b>3.</b> {{__('bonuses.welcome_eur9')}} <br>
            <b>4.</b> {{__('bonuses.welcome_eur10')}} <br>
            <b>{{__('bonuses.welcome_eur11')}}</b> <br>
            <b>(4.1)</b> {{__('bonuses.welcome_eur12')}}<br>
            <b>(4.2)</b> {{__('bonuses.welcome_eur13')}} <br>
            <b>5.</b> {{__('bonuses.welcome_eur14')}} <br>

        </p>


        <br><h5>{{__('bonuses.welcome_eur34')}}</h5>
        <p> {{__('bonuses.welcome_eur35')}} <br>
            {{__('bonuses.welcome_eur36')}} <b>{{__('bonuses.welcome_eur37')}}</b>)<br>
            <br>1. {{__('bonuses.welcome_eur38')}}<br>
            2. {{__('bonuses.welcome_eur39')}}<br>
            3. {{__('bonuses.welcome_eur40')}}<br>
            4. {{__('bonuses.welcome_eur41')}}<br>
            5. {{__('bonuses.welcome_eur42')}}<br>
        </p>
        @break
    @case(\App\UserWallet::CURRENCY_KZT)
        <h1>{{__('bonuses.welcome_kzt')}}</h1>
        <p>
            {{__('bonuses.welcome_kzt1')}} <br>
            <br><b>{{__('bonuses.welcome_kzt2')}}</b><br>
            <b>{{__('bonuses.welcome_kzt3')}}</b><br>
            <b>{{__('bonuses.welcome_kzt4')}}</b><br>
            <b>{{__('bonuses.welcome_kzt4_1')}}</b><br>
            <b>{{__('bonuses.welcome_kzt4_2')}}</b><br>
            <b>{{__('bonuses.welcome_kzt4_3')}}</b><br>
            <br>
        </p>
        <h5>{{__('bonuses.welcome_kzt5')}}</h5>
        <p>{{__('bonuses.welcome_kzt6')}} <br>
            <b>1.</b> {{__('bonuses.welcome_kzt7')}}<br>
            <b>2.</b> {{__('bonuses.welcome_kzt8')}}<b>{{__('bonuses.welcome_ru8_1')}}</b><br>
            <b>3.</b> {{__('bonuses.welcome_kzt9')}} <br>
            <b>4.</b> {{__('bonuses.welcome_kzt10')}} <br>
            <b>{{__('bonuses.welcome_kzt11')}}</b> <br>
            <b>(4.1)</b> {{__('bonuses.welcome_kzt12')}}<br>
            <b>(4.2)</b> {{__('bonuses.welcome_kzt13')}} <br>
            <b>5.</b> {{__('bonuses.welcome_kzt14')}} <br>

        </p>


        <br><h5>{{__('bonuses.welcome_kzt34')}}</h5>
        <p> {{__('bonuses.welcome_kzt35')}} <br>
            {{__('bonuses.welcome_kzt36')}} <b>{{__('bonuses.welcome_kzt37')}}</b>)<br>
            <br>1. {{__('bonuses.welcome_kzt38')}}<br>
            2. {{__('bonuses.welcome_kzt39')}}<br>
            3. {{__('bonuses.welcome_kzt40')}}<br>
            4. {{__('bonuses.welcome_kzt41')}}<br>
            5. {{__('bonuses.welcome_kzt42')}}<br>
        </p>
        @break
    @case(\App\UserWallet::CURRENCY_INR)
        <h1>{{__('bonuses.welcome_inr')}}</h1>
        <p>
            {{__('bonuses.welcome_inr1')}} <br>
            <br><b>{{__('bonuses.welcome_inr2')}}</b><br>
            <b>{{__('bonuses.welcome_inr3')}}</b><br>
            <b>{{__('bonuses.welcome_inr4')}}</b><br>
            <b>{{__('bonuses.welcome_inr4_1')}}</b><br>
            <b>{{__('bonuses.welcome_inr4_2')}}</b><br>
            <b>{{__('bonuses.welcome_inr4_3')}}</b><br>
            <br>
        </p>
        <h5>{{__('bonuses.welcome_inr5')}}</h5>
        <p>{{__('bonuses.welcome_inr6')}} <br>
            <b>1.</b> {{__('bonuses.welcome_inr7')}}<br>
            <b>2.</b> {{__('bonuses.welcome_inr8')}}<b>{{__('bonuses.welcome_inr8_1')}}</b><br>
            <b>3.</b> {{__('bonuses.welcome_inr9')}} <br>
            <b>4.</b> {{__('bonuses.welcome_inr10')}} <br>
            <b>{{__('bonuses.welcome_inr11')}}</b> <br>
            <b>(4.1)</b> {{__('bonuses.welcome_inr12')}}<br>
            <b>(4.2)</b> {{__('bonuses.welcome_inr13')}} <br>
            <b>5.</b> {{__('bonuses.welcome_inr14')}} <br>

        </p>


        <br><h5>{{__('bonuses.welcome_inr34')}}</h5>
        <p> {{__('bonuses.welcome_inr35')}} <br>
            {{__('bonuses.welcome_inr36')}} <b>{{__('bonuses.welcome_inr37')}}</b>)<br>
            <br>1. {{__('bonuses.welcome_inr38')}}<br>
            2. {{__('bonuses.welcome_inr39')}}<br>
            3. {{__('bonuses.welcome_inr40')}}<br>
            4. {{__('bonuses.welcome_inr41')}}<br>
            5. {{__('bonuses.welcome_inr42')}}<br>
        </p>
        @break
    @case(\App\UserWallet::CURRENCY_USDT)
        <h1>{{__('bonuses.welcome_usdt')}}</h1>
        <p>
            {{__('bonuses.welcome_usdt1')}} <br>
            <br><b>{{__('bonuses.welcome_usdt2')}}</b><br>
            <b>{{__('bonuses.welcome_usdt3')}}</b><br>
            <b>{{__('bonuses.welcome_usdt4')}}</b><br>
            <b>{{__('bonuses.welcome_usdt4_1')}}</b><br>
            <b>{{__('bonuses.welcome_usdt4_2')}}</b><br>
            <b>{{__('bonuses.welcome_usdt4_3')}}</b><br>
            <br>
        </p>
        <h5>{{__('bonuses.welcome_usdt5')}}</h5>
        <p>{{__('bonuses.welcome_usdt6')}} <br>
            <b>1.</b> {{__('bonuses.welcome_usdt7')}}<br>
            <b>2.</b> {{__('bonuses.welcome_usdt8')}}<b>{{__('bonuses.welcome_usdt8_1')}}</b><br>
            <b>3.</b> {{__('bonuses.welcome_usdt9')}} <br>
            <b>4.</b> {{__('bonuses.welcome_usdt10')}} <br>
            <b>{{__('bonuses.welcome_usdt11')}}</b> <br>
            <b>(4.1)</b> {{__('bonuses.welcome_usdt12')}}<br>
            <b>(4.2)</b> {{__('bonuses.welcome_usdt13')}} <br>
            <b>5.</b> {{__('bonuses.welcome_usdt14')}} <br>

        </p>


        <br><h5>{{__('bonuses.welcome_usdt34')}}</h5>
        <p> {{__('bonuses.welcome_usdt35')}} <br>
            {{__('bonuses.welcome_usdt36')}} <b>{{__('bonuses.welcome_usdt37')}}</b>)<br>
            <br>1. {{__('bonuses.welcome_usdt38')}}<br>
            2. {{__('bonuses.welcome_usdt39')}}<br>
            3. {{__('bonuses.welcome_usdt40')}}<br>
            4. {{__('bonuses.welcome_usdt41')}}<br>
            5. {{__('bonuses.welcome_usdt42')}}<br>
        </p>
        @break
    @default
@endswitch
