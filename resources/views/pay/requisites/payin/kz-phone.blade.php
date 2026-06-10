<label class="input__wrap_modal">
    <span class="input__label_modal">phone:</span>
    <input type="text" class="input__field_modal" placeholder="халықаралық телефон нөмірі (7....)" name="phone" value="{{$phone??'7'}}" required oninput="this.value = this.value.replaceAll(/[\s\D]/ig, ''); this.value = this.value.replaceAll(/^[^7]/ig, '7')">
</label>
@include('pay.requisites.payin.default', ['method' => $method])
