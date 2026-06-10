<style>
    .nashobmen-form .steps *{
        color: #FFFFFFDD;
        margin-top: 0.5rem;
        font-size: 0.8rem;
    }
    .nashobmen-form .btns{
        display: flex;
        width: 100%;
        justify-content: space-around;
    }

    .nashobmen-form .spinner .message{
        text-shadow: #0a0a17 2px 2px 12px;
        background-color: #0c0c3090;
        padding: 3rem;
        border-radius: 1rem;
    }

</style>
<div class="nashobmen-form cabinet__pad">
    <div class="cabinet__pad-title mt-2">{{__('cabinet.make_transfer')}} {{$amount}} {{$currency}}</div>
    <div class="flex mt-2">
            <div class="cabinet__prtn-ref">
                <div class="cabinet__prtn-ref-title">{{__('wallet.cardNumber')}}:</div>
                <div class="cabinet__prtn-ref-link"><span id="card_number">{{implode(' ', str_split(str_replace(' ', '', $card), 4))}}</span>
                    <div class="cabinet__prtn-ref-copy" onclick="copyCode('#card_number'); Toast.show('{{ __('Номер карты скопирован') }}')">
                        <img src="/img/ui/icons/cabinet/copy.svg">
                    </div>
                </div>
            </div>
        <div class="steps flex column jc-center ml-1">
            <div>{{__('cabinet.step1')}}</div>
            <div>{{__('cabinet.step2')}} {{$amount}} {{$currency}}.</div>
            <div>{{__('cabinet.step3')}}</div>
        </div>
    </div>
    <div class="flex mt-1 cabinet__pad-title jc-center ai-center" style="max-width: 35rem; word-break: break-word;">
        {{__('cabinet.pay_precise', ['amount' => $amount.$currency])}}
    </div>
    <div class="btns mt-1">
        <div data-url="" class="bonus__green-btn bonus_yes_btn" onclick="checkPayment();">
            {{__('cabinet.paid')}}
        </div>
        <div class="button button_outlined mt-1" onclick="Modal.hide(this.closest('.modal'))">
            {{__('cabinet.cancel')}}
        </div>
    </div>
</div>
<script>
    function copyCode(target) {
        if (typeof target === 'string') target = document.querySelector(target);
        let sel = document.getSelection();
        sel.removeAllRanges();
        let range = new Range();
        range.selectNode(target);
        sel.addRange(range);
        document.execCommand("copy");
    }
    async function checkPayment() {
        Spinner.show('.nashobmen-form');
        try {
            let res = await network.post('{{action([\App\PaymentsAPI\NashObmen\NashObmen::class, 'checkPayment'], ['trade' => $trade])}}');
            let msg = await res.text();
            await Spinner.hide('.nashobmen-form', msg, null, 2000);
            Modal.hide('.nashobmen-form');
        } catch (msg) {
            Spinner.hide('.nashobmen-form', msg, null, 3000);
        }
    }
</script>
