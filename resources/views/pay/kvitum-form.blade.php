<form id="brpay_form" name="brpay_form" method="{{$method??'GET'}}" action="{{$url}}">
    @foreach($data as $k => $v)
        <input type="hidden" name="{{$k}}" value="{{$v}}" />
    @endforeach
</form>
