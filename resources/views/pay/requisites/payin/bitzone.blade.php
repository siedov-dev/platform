{{--<label id="login-form-email" class="input__wrap_modal">--}}
{{--    <span class="input__label_modal">Банк:</span>--}}
{{--    <select class="custom-control cabinet__select" name="bank">--}}
{{--        @foreach($banks as $bank)--}}
{{--            <option value="{{$bank['value']}}">{{$bank['label']}}</option>--}}
{{--        @endforeach--}}
{{--    </select>--}}
{{--</label>--}}
<input id="fingerprint" type="hidden" name="fingerprint" value="">
@include('pay.requisites.payin.default', ['method' => $method])
<script>
    (
        async () => {
            const fingerprintVal = document.getElementById('fingerprint');
            const f = await generateFingerprint();
            console.log(f.hash);
            fingerprintVal.value =f.hash
        }
    )()
</script>
