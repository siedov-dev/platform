@php
$currency = request('currency', \App\UserWallet::CURRENCY_RUB)
@endphp
@switch($currency)
    @case(\App\UserWallet::CURRENCY_RUB)
        <h1>{{__('bonuses.welcome_ru')}}</h1>
        <p>
            {{__('bonuses.welcome_ru1')}} <br>
            <br><b>{{__('bonuses.welcome_ru2')}}</b><br>
            <b>{{__('bonuses.welcome_ru3')}}</b><br>
            <b>{{__('bonuses.welcome_ru4')}}</b><br>
            <br>
        </p>
        <h5>{{__('bonuses.welcome_ru5')}}</h5>
        <p>{{__('bonuses.welcome_ru6')}} <br>
            <b>1.</b> {{__('bonuses.welcome_ru7')}}<br>
            <b>2.</b> {{__('bonuses.welcome_ru8')}}<b>{{__('bonuses.welcome_ru8_1')}}</b><br>
            <b>3.</b> {{__('bonuses.welcome_ru9')}} <br>
            <b>4.</b> {{__('bonuses.welcome_ru10')}} <b>{{__('bonuses.welcome_ru11')}}</b> <br>
            <b>(4.1)</b> {{__('bonuses.welcome_ru12')}}<br>
            <b>(4.2)</b> {{__('bonuses.welcome_ru13')}} <br>
            <b>5.</b> {{__('bonuses.welcome_ru14')}} <br>

        </p>


        <br><h5>{{__('bonuses.welcome_ru15')}}</h5>
        <p>{{__('bonuses.welcome_ru16')}} <br>
            <b>1.</b> {{__('bonuses.welcome_ru17')}}<br>
            <b>2.</b> {{__('bonuses.welcome_ru18')}} <br>
            <b>3.</b> {{__('bonuses.welcome_ru19')}} <b>{{__('bonuses.welcome_ru20')}}</b> <br>
            <b>(3.1)</b>{{__('bonuses.welcome_ru21')}} <br>
            <b>(3.2)</b> {{__('bonuses.welcome_ru22')}} <br>
            <b>4.</b> {{__('bonuses.welcome_ru23')}} <br>

        </p>


        <br><h5>{{__('bonuses.welcome_ru24')}}</h5>
        <p>{{__('bonuses.welcome_ru25')}} <br>
            <b>1.</b> {{__('bonuses.welcome_ru26')}} <b>{{__('bonuses.welcome_ru27')}}</b><br>
            <b>2.</b> {{__('bonuses.welcome_ru28')}} <br>
            <b>3.</b> {{__('bonuses.welcome_ru29')}} <b>{{__('bonuses.welcome_ru30')}}</b> <br>
            <b>(3.1)</b> {{__('bonuses.welcome_ru31')}}<br>
            <b>(3.2)</b> {{__('bonuses.welcome_ru32')}} <br>
            <b>4.</b> {{__('bonuses.welcome_ru33')}} <br>

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
        CURRENCY_UAH
        @break
    @case(\App\UserWallet::CURRENCY_EUR)
        CURRENCY_EUR
        @break
    @case(\App\UserWallet::CURRENCY_INR)
        CURRENCY_INR
        @break
    @case(\App\UserWallet::CURRENCY_USDT)
        CURRENCY_USDT
        @break
    @case(\App\UserWallet::CURRENCY_KZT)
        CURRENCY_KZT
        @break
@endswitch
