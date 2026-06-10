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
        </div>

        <div class="footer__line"></div>

        <img src="/img/partners/all.png" class="footer__partners">

        <div class="footer__line"></div>

        <div class="footer__logo">
            <div class="footer__certificate">
                <a href="https://cert.gcb.cw/certificate?id=ZXlKcGRpSTZJbWRRWldSd2NXODJUMWd5UjFoSlpsRlliRWc1YlZFOVBTSXNJblpoYkhWbElqb2lNRFFyUjJwME1uRXpRVUZyZHprNVYwWTNTMEZtZHowOUlpd2liV0ZqSWpvaU5EUXlZelkzWm1WbE1qUmpNMkl6WlRaaE5ERmlObVk0WlRneU0yTm1ZV1F4TlRNek16RTFaVE5sWm1JNVkyTm1ZakV3WkRRek0yWmxPVEZsTlRBd1lTSXNJblJoWnlJNklpSjk=" target="_blank">
                    <img src="/img/GCB_Seal.svg">
                </a>
            </div>
        </div>

        <div class="footer__license-text">
            {{ __('footer.license') }}
        </div>

    </div>

</div>
