<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">Email:</span>
    <input type="text" class="input__field_modal" placeholder="customer email..." name="email" value="{{$email??''}}" required>
</label>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">Phone (10 numbers after 91):</span>
    <input type="text" class="input__field_modal" placeholder="customer phone..." name="phone" value="{{$mobile??''}}" required>
</label>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">First name:</span>
    <input type="text" class="input__field_modal" placeholder="customer name..." name="first_name" value="{{$name??''}}" required>
</label>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">Last name:</span>
    <input type="text" class="input__field_modal" placeholder="customer surname..." name="last_name" value="{{$surname??''}}" required>
</label>
<div class="input__wrap_modal">
    <span class="input__label_modal">bank:</span>
    <select class="option__field_modal" name="mop">
        <option value='1009'>Bank of India</option>
        <option value='1000'>Allahabad Bank</option>
        <option value='1004'>HDFC Bank</option>
        <option value='1135'>AU small finance bank</option>
        <option value='1005'>Axis Bank</option>
        <option value='1064'>Bank of Maharashtra</option>
        <option value='1013'>ICICI Bank</option>
        <option value='1025'>Canara Bank</option>
        <option value='1094'>Catholic Syrian Bank</option>
        <option value='1069'>Indian Bank</option>
        <option value='1027'>Federal Bank</option>
        <option value='1034'>Corporation Bank</option>
        <option value='1063'>Central Bank of India</option>
        <option value='1040'>DCB Bank - Personal</option>
        <option value='1043'>City Union Bank</option>
        <option value='1126'>Dhanlakshmi Bank</option>
        <option value='1026'>Deutsche Bank</option>
        <option value='1003'>IDBI Bank</option>
        <option value='1120'>IDFC Bank</option>
        <option value='1049'>Indian Overseas Bank</option>
        <option value='1054'>IndusInd Bank</option>
        <option value='1041'>Jammu and Kashmir Bank</option>
        <option value='1032'>Karnataka Bank Ltd</option>
        <option value='1048'>Karur Vysya Bank</option>
        <option value='1042'>Oriental Bank of Commerce</option>
        <option value='1012'>Kotak Mahindra Bank</option>
        <option value='1030'>State Bank of India</option>
        <option value='1053'>Ratnakar Bank</option>
        <option value='1050'>State Bank of Bikaner and Jaipur</option>
        <option value='1095'>Laxmi Vilas Bank - Retail Net Banking</option>
        <option value='1129'>Punjab National Bank - Retail Net Banking</option>
        <option value='1046'>United Bank of India</option>
        <option value='1091'>Andhra Bank</option>
        <option value='1096'>Punjab National Bank Corporate Account</option>
        <option value='1093'>Bank of Baroda Retail Accounts</option>
        <option value='1044'>Vijaya Bank</option>
        <option value='1045'>South Indian Bank</option>
        <option value='1103'>UCO Bank</option>
        <option value='1038'>Union Bank of India</option>
        <option value='1001'>Yes Bank Ltd</option>
    </select>
</div>

@include('pay.requisites.payin.default', ['method' => $method])
