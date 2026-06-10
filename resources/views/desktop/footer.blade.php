<div class="footer">
    <div class="width-limiter flex column">

        <div class="footer__links-section">

            @foreach(\App\Models\FooterLink::getLinks(\Illuminate\Support\Facades\App::getLocale()) as $group => $links)
                <div class="footer__links-column">
                    <div class="footer__links-items">
                        @foreach($links as $link)
                            @foreach($link as $id => $name)
                                <a href="{{ route('footer.page', ['id' => $id], false) }}" class="footer__link content-link">{{ $name }}</a>
                            @endforeach
                        @endforeach
                    </div>
                </div>
            @endforeach

            <aside class="footer__support-pad">
                @include('common.blocks.chat-panel')
            </aside>


            {{--            <div class="footer__subscribes">--}}

            {{--            </div>--}}


            {{--            <div class="footer__certificates">--}}

            {{--            </div>--}}
        </div>

        <div class="footer__line"></div>

        <img src="/img/partners/all.png" class="footer__partners">

        <div class="footer__line"></div>

        <!--<div class="footer__bottom">
            <div class="footer__copyright">
                © {{ date('Y') }} Cassino<br/>
                {{ __('footer.allRightsReserved') }}
        </div>-->

        {{--            <div class="footer__logo">--}}
        {{--                KRAKEN--}}
        {{--                <img src="/img/brand/logo.svg" class="footer__logo-image">--}}
        {{--                CASINO--}}
        {{--            </div>--}}

{{--            @if(preg_match('#^http[s]?://krakencasino\.club.*$#', request()->url()))--}}
{{--                <div class="footer__certificate" title="Antillephone License Validation">--}}
{{--                    <div id="apg-07954048-1cc4-4165-908c-c350625ae778" data-apg-seal-id="07954048-1cc4-4165-908c-c350625ae778" data-apg-image-size="128" data-apg-image-type="basic-small">--}}
{{--                    </div>--}}
{{--                    <script src="https://07954048-1cc4-4165-908c-c350625ae778.snippet.antillephone.com/apg-seal.js" type="text/javascript"></script>--}}
{{--                </div>--}}
{{--            @else--}}
{{--            <!--<div class="footer__certificate">--}}
{{--                    <a href="/img/certificate.pdf" onclick="event.preventDefault(); Modal.show('<iframe src=\'/img/certificate.pdf\' style=\'width: calc(100vw - 2rem);height: calc(100vh - 2rem);border: none;\'></iframe>').querySelector('.modal').style.marginTop = '1rem'">--}}
{{--                        <img src="/img/certificate-icon.png" width="60">--}}
{{--                    </a>--}}
{{--                </div>-->--}}
{{--            @endif--}}
{{--        </div>--}}

        <div class="footer__license-text">
            {{ __('footer.license') }}
        </div>

         <div class="footer__logo">
             <div class="footer__certificate">
                 <a href="https://cert.gcb.cw/certificate?id=ZXlKcGRpSTZJbWRRWldSd2NXODJUMWd5UjFoSlpsRlliRWc1YlZFOVBTSXNJblpoYkhWbElqb2lNRFFyUjJwME1uRXpRVUZyZHprNVYwWTNTMEZtZHowOUlpd2liV0ZqSWpvaU5EUXlZelkzWm1WbE1qUmpNMkl6WlRaaE5ERmlObVk0WlRneU0yTm1ZV1F4TlRNek16RTFaVE5sWm1JNVkyTm1ZakV3WkRRek0yWmxPVEZsTlRBd1lTSXNJblJoWnlJNklpSjk=" target="_blank">
                     <img src="/img/GCB_Seal.svg" width="100">
                 </a>
             </div>
            <img src="/img/18+.svg.png" class="footer__logo-image" width="50">
        </div>

    </div>
</div>
