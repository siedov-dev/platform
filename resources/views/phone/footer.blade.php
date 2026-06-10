<div class="footer">
    <div class="width-limiter">

        <div class="flex jc-sb ai-center">
            <div class="landing__footer-logo mr-1">
                @include('common.blocks.logo')
            </div>
            <div class="navigation-wrapper">
                <div class="button navigation-btn" onclick="document.dispatchEvent(new Event('showNavigation'))">
                    {{__('footer.navigation')}}
                    <div class="arrow-down small"></div>
                </div>
            </div>
        </div>

        <aside class="footer__support-pad mt-1">
            @include('common.blocks.chat-panel')
        </aside>

        <div class="flex jc-sa">
            <img src="/img/icons/visa.png" class="invert-white">
            <img src="/img/icons/mastercard.png">
            <img src="/img/icons/applepay.png" class="invert-white">
            <img src="/img/icons/gpay.png">
            <img src="/img/icons/bitcoin.png">
            <img src="/img/icons/tether.png">
        </div>

        <!--<div class="footer__license-text">
            {{ __('footer.license') }}
        </div>-->


        <div class="footer__bottom mt-1">
            <div class="footer__logo">
                <div class="footer__certificate">
                    <a href="https://cert.gcb.cw/certificate?id=ZXlKcGRpSTZJbWRRWldSd2NXODJUMWd5UjFoSlpsRlliRWc1YlZFOVBTSXNJblpoYkhWbElqb2lNRFFyUjJwME1uRXpRVUZyZHprNVYwWTNTMEZtZHowOUlpd2liV0ZqSWpvaU5EUXlZelkzWm1WbE1qUmpNMkl6WlRaaE5ERmlObVk0WlRneU0yTm1ZV1F4TlRNek16RTFaVE5sWm1JNVkyTm1ZakV3WkRRek0yWmxPVEZsTlRBd1lTSXNJblJoWnlJNklpSjk=" target="_blank">
                        <img src="/img/GCB_Seal.svg">
                    </a>
                </div>
            </div>

            <div class="footer__copyright">
                © {{ date('Y') }} Cassino<br/>
                {{ __('footer.allRightsReserved') }}
            </div>

{{--            <div class="footer__logo">--}}
{{--                KRAKEN--}}
{{--                <img src="/img/brand/logo.svg" class="footer__logo-image">--}}
{{--                CASINO--}}
{{--            </div>--}}


{{--           <div class="footer__logo">--}}
{{--               <div class="footer__certificate">--}}
{{--                   <a href="https://licensing.igaming-validator.info/License%20Validation.html" target="_blank">--}}
{{--                       <img src="/img/cert-icon.png" width="100">--}}
{{--                   </a>--}}
{{--               </div>--}}
{{--           </div>--}}

            @if(preg_match('#^http[s]?://krakencasino\.club.*$#', request()->url()))
                <!--<div class="footer__certificate" title="Antillephone License Validation">
                    <div id="apg-07954048-1cc4-4165-908c-c350625ae778" data-apg-seal-id="07954048-1cc4-4165-908c-c350625ae778" data-apg-image-size="128" data-apg-image-type="basic-small">
                    </div>-->
                   <!-- <script src="https://07954048-1cc4-4165-908c-c350625ae778.snippet.antillephone.com/apg-seal.js" type="text/javascript"></script>
                </div>-->
            @else
                <!--<div class="footer__certificate">
                    <a href="/img/certificate.pdf" onclick="event.preventDefault(); Modal.show('<iframe src=\'/img/certificate.pdf\' style=\'width: calc(100vw - 2rem);height: calc(100vh - 2rem);border: none;\'></iframe>').querySelector('.modal').style.marginTop = '1rem'">
                        <img src="/img/certificate-icon.png" width="60">
                    </a>
                </div>-->
            @endif
        </div>

        </div>


    </div>
{{--</div>--}}
