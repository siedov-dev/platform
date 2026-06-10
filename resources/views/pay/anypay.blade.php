<form action="https://secure.anypay.uk/pay" method="post" >
    <input type="hidden" name="data" value="{{ $data }}">
    <input type="hidden" name="sign" value="{{ $sign }}">
    <button type="submit" value="Make payment" style="display: none;"> Make payment </button>
</form>
