    <form id="brpay_form" name="brpay_form" method="POST" action="{{$url}}">
        <input type="hidden" name="sp_outlet_id" value="{{$sp_outlet_id}}" />
        <input type="hidden" name="sp_order_id" value="{{$sp_order_id}}" />
        <input type="hidden" name="sp_user_name" value="{{$sp_user_name}}" />
        <input type="hidden" name="sp_user_phone" value="{{$sp_user_phone}}" />
        <input type="hidden" name="sp_user_contact_email" value="{{$sp_user_contact_email}}" />
        <input type="hidden" name="sp_amount" value="{{$sp_amount}}" />
        <input type="hidden" name="sp_currency" value="{{$sp_currency}}" />
        <input type="hidden" name="sp_description" value="{{$sp_description}}" />
        <input type="hidden" name="sp_user_ip" value="{{request()->ip()}}" />
        <input type="hidden" name="sp_lifetime" value="{{$sp_lifetime}}" />
        <input type="hidden" name="sp_salt" value="{{$sp_salt}}" />
        <input type="hidden" name="sp_sig" value="{{$sp_sig}}" />
        <input type="hidden" name="sp_payment_system" value="{{$sp_payment_system}}" />
    </form>
