<form name="payment" method="post" action="https://sci.interkassa.com/" accept-charset="UTF-8">
    <input type="hidden" name="ik_co_id" value="{{$ik_co_id}}"/>
    <input type="hidden" name="ik_pm_no" value="{{$ik_pm_no}}"/>
    <input type="hidden" name="ik_am" value="{{$ik_am}}"/>
    <input type="hidden" name="ik_cur" value="{{$ik_cur}}"/>
    <input type="hidden" name="ik_desc" value="{{$ik_desc}}"/>
    <input type="hidden" name="ik_sign" value="{{$ik_sign}}"/>
    <input type="submit" value="Pay">
</form>
