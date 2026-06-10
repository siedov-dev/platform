<form name="brpay_form" method="POST" action="{{$url}}">
    @foreach($params as $k => $v)
        <input type="hidden" name="{{$k}}" value="{{$v}}" />
    @endforeach
</form>
