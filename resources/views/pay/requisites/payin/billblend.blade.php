<label class="input__wrap_modal">
    <span class="input__label_modal">First name:</span>
    <input type="text" class="input__field_modal" placeholder="First name" name="first_name" required="" value="{{ request()->cookie('bill_first_name') }}">
</label>
<label class="input__wrap_modal">
    <span class="input__label_modal">Last name:</span>
    <input type="text" class="input__field_modal" placeholder="Last name" name="last_name" required="" value="{{ request()->cookie('bill_last_name') }}">
</label>
<label class="input__wrap_modal">
    <span class="input__label_modal">Email:</span>
    <input type="email" autocomplete="email" class="input__field_modal" placeholder="Email" name="email" value="{{ Auth::user()->email }}" required="">
</label>
<label class="input__wrap_modal">
    <span class="input__label_modal">Phone:</span>
    <input type="text" class="input__field_modal" placeholder="Phone" name="phone" required="" value="{{ request()->cookie('bill_phone') }}">
</label>
<label class="input__wrap_modal">
    <span class="input__label_modal">Address1:</span>
    <input type="text" class="input__field_modal" placeholder="Address1" name="address1" required="" value="{{ request()->cookie('bill_address1') }}">
</label>
<label class="input__wrap_modal">
    <span class="input__label_modal">City:</span>
    <input type="text" class="input__field_modal" placeholder="City" name="city" required="" value="{{ request()->cookie('bill_city') }}">
</label>
<label class="input__wrap_modal">
    <span class="input__label_modal">Zip code:</span>
    <input type="text" class="input__field_modal" placeholder="Zip code" name="zip_code" required="" value="{{ request()->cookie('bill_zip_code') }}">
</label>
<label class="input__wrap_modal">
    <span class="input__label_modal">Country:</span>
    <select name="country" class="input__field_modal" required="">
        @foreach ($countries as $key => $country)
            <option value="{{$key}}" @if(request()->cookie('bill_country') == $key) @selected(true) @endif>{{ $country }}</option>
        @endforeach
    </select>
</label>