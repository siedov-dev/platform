<label class="input__wrap_modal">
    <span class="input__label_modal">name:</span>
    <input type="text" class="input__field_modal" placeholder="John Doe" name="name" value="{{$name??''}}" required>
</label>
<label class="input__wrap_modal">
    <span class="input__label_modal">email:</span>
    <input type="email" class="input__field_modal" placeholder="email@domain.com" name="email" value="{{$email??''}}" required>
</label>
@include('pay.requisites.payin.default', ['method' => $method])
