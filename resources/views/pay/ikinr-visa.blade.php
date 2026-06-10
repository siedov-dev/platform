<form name="payment" method="{{$method}}" action="{{$action}}">
    <input type="hidden" name="x-request-id" value="{{$x_request_id}}"/>
    <input type="hidden" name="ik_suc_u" value="{{$ik_suc_u??''}}"/>
    <input type="hidden" name="ik_fal_u" value="{{$ik_fal_u??''}}"/>
</form>
