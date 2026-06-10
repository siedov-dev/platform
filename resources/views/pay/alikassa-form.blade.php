    <form id="ali_form" name="bl_form" method="POST" action="https://sci.alikassa.com/payment">
    <input type="hidden" name="merchantUuid" value="{{$merchantUuid}}" />
    <input type="hidden" name="desc" value="Пополнение Krakencassino" />
    <input type="hidden" name="orderId" value="{{$orderId}}" />
    <input type="hidden" name="amount" value="{{$amount}}" />
    <input type="hidden" name="currency" value="{{$currency}}" />
{{--    <input type="hidden" name="payWayOn" value="Qiwi,YandexMoney,Card,AliKassa" />--}}
{{--    <input type="hidden" name="customerIp" value="{{request()->ip()}}" />--}}
</form>
