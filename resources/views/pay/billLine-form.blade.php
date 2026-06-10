<form id="bl_form" name="bl_form" method="POST" action="{{$url}}/payment/form">
    <input type="hidden" name="merchant" value="{{$merchant}}" />
    <input type="hidden" name="item_name" value="Пополнение" />
    <input type="hidden" name="order" value="{{$order}}" />
    <input type="hidden" name="amount" value="{{$amount}}" />
    <input type="hidden" name="currency" value="{{$currency}}" />
    <input type="hidden" name="first_name" value="{{$first_name??''}}" />
    <input type="hidden" name="last_name" value="{{$last_name??''}}" />
    <input type="hidden" name="country" value="UA" />
    <input type="hidden" name="user_id" value="{{\Illuminate\Support\Facades\Auth::id()}}" />
    <input type="hidden" name="ip" value="{{request()->ip()}}" />
    <input type="hidden" name="custom" value="{{$custom??''}}" />
    <input type="hidden" name="payment_url" value="{{action('IndexController@index')}}" />
    <input type="hidden" name="success_url" value="{{action('PayController@success')}}" />
    <input type="hidden" name="fail_url" value="{{action('PayController@fail')}}" />
    <input type="hidden" name="lang" value="{{\Illuminate\Support\Facades\App::getLocale()}}" />
</form>
