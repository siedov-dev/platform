<form action="{{$url}}" method=POST>
    @foreach($data as $k => $v)
        <input type="hidden" name="{{$k}}" value="{{ $v }}">
    @endforeach
    <input type="submit" value="Оплатить">
</form>
