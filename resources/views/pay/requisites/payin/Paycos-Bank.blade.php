<label id="login-form-email" class="input__wrap_modal">
    <span class="input__label_modal">bank:</span>
    <select class="option__field_modal" name="mop_type">
        <option value="1091">Andhra Bank</option>
        <option value="1110">Allahabad Bank</option>
        <option value="1005">Axis Bank</option>
        <option value="1099">Axis Bank Corporate</option>
        <option value="1029">ABN Amro Bank</option>
        <option value="1043">Bank of Bahrain And Kuwait</option>
        <option value="1092">Bank of Baroda Corporate</option>
        <option value="1093">Bank of Baroda Retail Accounts</option>
        <option value="1009">Bank of India</option>
        <option value="1064">Bank of Maharashtra</option>
        <option value="1055">Canara Bank</option>
        <option value="1094">Catholic Syrian Bank</option>
        <option value="1063">Central Bank of India</option>
        <option value="1010">Citi Bank</option>
        <option value="1060">City Union Bank</option>
        <option value="1034">Corporation Bank</option>
        <option value="1103">COSMOS Bank</option>
        <option value="1040">DCB Bank</option>
        <option value="1292">DCB Bank Corporate</option>
        <option value="1026">Deutsche Bank</option>
        <option value="1070">Dhanlaxmi Bank</option>
        <option value="1040">Development Credit Bank</option>
        <option value="1106">Equitas Bank</option>
        <option value="1027">Federal Bank</option>
        <option value="1004">HDFC Bank</option>
        <option value="1102">HSBC Bank</option>
        <option value="1013">ICICI Bank</option>
        <option value="1100">ICICI Bank Corporate</option>
        <option value="1107">IDFC FIRST Bank Limited</option>
        <option value="1069">Indian Bank</option>
        <option value="10491">Indian Overseas Bank</option>
        <option value="1054">Indusind Bank</option>
        <option value="1003">Industrial Development Bank of India</option>
        <option value="1062">IngVysya Bank</option>
        <option value="1041">Jammu And Kashmir Bank</option>
        <option value="1072">Janata Sahakari Bank Pune</option>
        <option value="1032">Karnatka Bank Ltd</option>
        <option value="1048">KarurVysya Bank</option>
        <option value="1012">Kotak Bank</option>
        <option value="1095">Lakshmi Vilas Bank NetBanking</option>
        <option value="1042">Oriental Bank of Commerce</option>
        <option value="1296">Punjab and Sindh Bank</option>
        <option value="1002">Punjab National Bank</option>
        <option value="1101">Punjab National Bank Corporate</option>
        <option value="1053">Ratnakar Bank (RBL Bank)</option>
        <option value="1056">SaraSwat Bank</option>
        <option value="1045">South Indian Bank</option>
        <option value="1097">Standard Chartered Bank</option>
        <option value="1050">State Bank of Bikaner And Jaipur</option>
        <option value="1039">State Bank of Hyderabad</option>
        <option value="1030">State Bank of India</option>
        <option value="1037">State Bank of Mysore</option>
        <option value="1068">State Bank of Patiala</option>
        <option value="1061">State Bank of Travancore</option>
        <option value="1098">Syndicate Bank</option>
        <option value="1065">Tamilnad Mercantile Bank</option>
        <option value="1103">UCO Bank</option>
        <option value="1038">Union Bank of India</option>
        <option value="1046">United Bank of India</option>
        <option value="1044">Vijay Bank</option>
        <option value="1001">Yes Bank</option>
    </select>
</label>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">Email:</span>
    <input type="text" class="input__field_modal" placeholder="customer email..." name="email" value="{{$email}}" required>
</label>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">Phone (10 numbers after 91):</span>
    <input type="text" class="input__field_modal" placeholder="customer phone..." name="phone" value="{{$mobile}}" required>
</label>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">address:</span>
    <input type="text" class="input__field_modal" placeholder="customer address..." name="address" value="{{$address??''}}" required>
</label>
<input type="hidden" name="ip" value="{{request()->ip()}}">
@include('pay.requisites.payin.default', ['method' => $method])
