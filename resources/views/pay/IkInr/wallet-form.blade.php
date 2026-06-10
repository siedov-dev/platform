<form name="payment" method="post" action="https://sci.interkassa.com/" accept-charset="UTF-8">
    <input type="hidden" name="ik_co_id" value="{{$ik_co_id}}"/>
    <input type="hidden" name="ik_pm_no" value="{{$ik_pm_no}}"/>
    <input type="hidden" name="ik_am" value="{{$ik_am}}"/>
    <input type="hidden" name="ik_cur" value="{{$ik_cur}}"/>
    <input type="hidden" name="ik_desc" value="{{$ik_desc}}"/>
    <input type="hidden" name="ik_pw_via" value="{{$ik_pw_via}}"/>
    <input type="hidden" name="ik_x_mop_type" value="{{$ik_x_mop_type}}"/>
    <input type="hidden" name="ik_x_customer_phone" value="{{$ik_x_customer_phone}}"/>
    <input type="hidden" name="ik_x_customer_email" value="{{$ik_x_customer_email}}"/>
    <input type="hidden" name="ik_sign" value="{{$ik_sign}}"/>
    <input type="hidden" name="ik_suc_u" value="{{$ik_suc_u??''}}"/>
    <input type="hidden" name="ik_fal_u" value="{{$ik_fal_u??''}}"/>
    <input type="submit" value="Pay">
</form>
