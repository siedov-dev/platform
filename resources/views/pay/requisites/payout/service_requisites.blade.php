@php
    $pay_system = \App\Models\PaymentSystems::getPayoutSystemForMethod($method);
    $min_payout = $pay_system->getMinSum();
    $max_payout = $pay_system->getMaxSum();
@endphp
<div style="display: none" id="max-payout-value">{{$max_payout}}</div>
@foreach($requisites as $requisite)
    @switch($requisite['type']?? 'string')
        @case('string')
            <label class="input__wrap_modal mt-2">
                <span class="input__label_modal wallet__requisites-label">{{ __('requisites.'.$requisite['name']) }}:</span>
                <input type="text" class="input__field_modal" placeholder="{{ __('requisites.'.$requisite['name']) }}" name="{{$requisite['name']}}" required>
            </label>
        @break
        @case('cardNumber')
            <label class="input__wrap_modal mt-2">
                <span class="input__label_modal wallet__requisites-label">{{ __('requisites.'.$requisite['name']) }}:</span>
                <input type="text" inputmode="numeric" pattern="\d{12,19}" autocomplete="cc-number" class="input__field_modal" placeholder="{{__('wallet.cardNumber')}}" name="cardNumber" required oninput="this.value = this.value.replaceAll(/[\s-]/ig, '')">
            </label>
        @break
        @case('phone')
            <label class="input__wrap_modal mt-2">
                <span class="input__label_modal">{{__('requisites.phone')}}:</span>
                <input type="tel" class="input__field_modal" placeholder="77712345678" name="{{$requisite['name']}}" value="{{auth()?->phone??''}}" autocomplete="tel" required oninput="this.value = this.value.replaceAll(/[^\d]/ig, '')">
            </label>
        @break
        @case('select')
            <label class="input__wrap_modal mt-2">
                <span class="input__label_modal"> {{__('requisites.'.$requisite['name'])}}: </span>
                <select class="custom-control cabinet__select w-100" name="{{ $requisite['name'] }}" required>
                    @php
                        asort($requisite['options'])
                    @endphp
                    @foreach($requisite['options'] as $option => $name)
                        <option value="{{$option}}">{{$name}}</option>
                    @endforeach
                </select>
            </label>
        @break
    @endswitch
@endforeach
