<label class="input__wrap_modal mt-2">
    <span class="input__label_modal">Email:</span>
    <input type="text" class="input__field_modal" placeholder="customer email..." name="email" value="{{$email??''}}" required>
</label>
<label class="input__wrap_modal">
    <span class="input__label_modal">Ім'я:</span>
    <input type="text" class="input__field_modal" placeholder="first name" name="from_first_name" value="{{$from_first_name??''}}" required>
</label>
<label class="input__wrap_modal">
    <span class="input__label_modal">Прізвище:</span>
    <input type="text" class="input__field_modal" placeholder="last name" name="from_last_name" value="{{$from_last_name??''}}" required>
</label>
<label class="input__wrap_modal">
    <span class="input__label_modal">Вулиця:</span>
    <input type="text" class="input__field_modal" placeholder="customer street..." name="billing_street" value="{{$billing_street??''}}" required>
</label>
<label class="input__wrap_modal">
    <span class="input__label_modal">Місто:</span>
    <input type="text" class="input__field_modal" placeholder="customer town..." name="billing_town" value="{{$billing_town??''}}" required>
</label>
<label class="input__wrap_modal">
    <span class="input__label_modal">Країна:</span>
    <input type="text" class="input__field_modal" placeholder="customer country..." name="from_country" value="{{$from_country??'Ukraine'}}" required>
</label>
<label class="input__wrap_modal">
    <span class="input__label_modal">Поштовий код:</span>
    <input type="text" class="input__field_modal" placeholder="customer post_code..." name="billing_post_code" value="{{$billing_post_code??''}}" required>
</label>
@include('pay.requisites.payin.default', ['method' => $method])
