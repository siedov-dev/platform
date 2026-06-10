<form action="https://perfectmoney.com/api/step1.asp" method="POST" id="pm_form">
    {{csrf_field()}}
    <input type="hidden" name="PAYEE_ACCOUNT" value="U21637049">
    <input type="hidden" name="PAYEE_NAME" value="krakencassino">
    <input type="hidden" name="PAYMENT_ID" value="{{$transaction_id}}">
    <input type="hidden" name="PAYMENT_AMOUNT" value="{{$amount}}">
        <input type="hidden" name="PAYMENT_UNITS" value="USD">
        <input type="hidden" name="STATUS_URL" value="">
        <input type="hidden" name="PAYMENT_URL" value="{{action('PayController@perfect_money_success')}}">
        <input type="hidden" name="PAYMENT_URL_METHOD" value="POST">
        <input type="hidden" name="NOPAYMENT_URL" value="{{action('PayController@perfect_money_error')}}">
        <input type="hidden" name="NOPAYMENT_URL_METHOD" value="POST">
        <input type="hidden" name="SUGGESTED_MEMO" value="">
        <input type="hidden" name="BAGGAGE_FIELDS" value="">
        <input type="hidden" name="INTERFACE_LANGUAGE" value="ru_RU">
</form>
