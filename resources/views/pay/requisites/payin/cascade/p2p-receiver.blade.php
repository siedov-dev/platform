<style>
    .p2p-form .steps *{
        color: #FFFFFFDD;
        margin-top: 0.5rem;
        font-size: 0.8rem;
    }
    .p2p-form .btns{
        display: flex;
        width: 100%;
        justify-content: space-around;
    }

    .p2p-form .spinner .message{
        text-shadow: #0a0a17 2px 2px 12px;
        background-color: #0c0c3090;
        padding: 3rem;
        border-radius: 1rem;
    }

</style>
<div class="p2p-form cabinet__pad">
    <div class="cabinet__pad-title mt-2">{{__('cabinet.make_transfer')}} {{$p2p_data['amount']}} {{$p2p_data['currency']??''}}</div>
    <div class="flex mt-2">
        <div class="cabinet__prtn-ref">
            <div class="cabinet__prtn-ref-title">{{__('wallet.receiver')}}:</div>
            <div class="cabinet__prtn-ref-link"><span id="card_number">{{$p2p_data['receiver']}}</span>
                <div class="cabinet__prtn-ref-copy" onclick="copyCode('#card_number'); Toast.show('{{ __('скопирован') }}')">
                    <img src="/img/ui/icons/cabinet/copy.svg">
                </div>
            </div>
            @if(!empty($p2p_data['bank_name']))
                <div class="cabinet__prtn-ref-title">{{__('wallet.bank_name')}}:</div>
                <div class="cabinet__prtn-ref-link"><span id="bank_name">{{$p2p_data['bank_name']}}</span>
                    <div class="cabinet__prtn-ref-copy" onclick="copyCode('#bank_name'); Toast.show('{{ __('скопирован') }}')">
                        <img src="/img/ui/icons/cabinet/copy.svg">
                    </div>
                </div>
            @endif
            @if(!empty($p2p_data['person']))
                <div class="cabinet__prtn-ref-title">{{__('wallet.person')}}:</div>
                <div class="cabinet__prtn-ref-link"><span id="person">{{$p2p_data['person']}}</span>
                    <div class="cabinet__prtn-ref-copy" onclick="copyCode('#person'); Toast.show('{{ __('скопирован') }}')">
                        <img src="/img/ui/icons/cabinet/copy.svg">
                    </div>
                </div>
            @endif
        </div>
        <div class="steps flex column jc-center ml-1">
            <div>{{__('cabinet.step1')}}</div>
            <div>{{__('cabinet.step2')}} {{$p2p_data['amount']}} {{$p2p_data['currency']}}.</div>
            <div>{{__('cabinet.step3')}}</div>
        </div>
    </div>
    <div class="flex mt-1 cabinet__pad-title jc-center ai-center" style="max-width: 35rem; word-break: break-word;">
        {{__('cabinet.pay_precise', ['amount' => $p2p_data['amount'].$p2p_data['currency']])}}
    </div>
    <div class="btns mt-1">
        <div data-url="" class="bonus__green-btn bonus_yes_btn" onclick="confirmPayment()">
            {{__('cabinet.paid')}}
        </div>
        <div class="button button_outlined mt-1" onclick="cancelPayment()">
            {{__('cabinet.cancel')}}
        </div>
    </div>
</div>
<script>
    async function confirmPayment() {

        Spinner.show('.p2p-form');
        try {
            await network.post('{{route('deposit.confirm',[], false)}}', {
                transaction_id: {{$p2p_data['transaction_id']}}
            })
            Spinner.hide('.p2p-form', "", null, 2000);
            Modal.hide('.p2p-form')
        } catch (msg) {
            Spinner.hide('.p2p-form', msg, null, 3000);
        }
    }

    async function cancelPayment() {

        Spinner.show('.p2p-form');
        try {
            await network.post('{{route('deposit.cancel',[], false)}}', {
                transaction_id: {{$p2p_data['transaction_id']}}
            })
            Spinner.hide('.p2p-form', "", null, 2000);
            Modal.hide('.p2p-form')
        } catch (msg) {
            Spinner.hide('.p2p-form', msg, null, 3000);
        }
    }
</script>
