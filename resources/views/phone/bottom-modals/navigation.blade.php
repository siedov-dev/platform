<div class="navigation-menu bottom-modal">
    <div class="content">
        <div class="pad">
            <h2 class="bottom-modal__title mb-1">{{__('footer.navigation')}}</h2>
            <nav class="footer__links-section">
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
{{--                <div class="footer__links-column">--}}
{{--                    <div class="footer__links-title">{{ __('footer.games') }}</div>--}}
{{--                    <div class="footer__links-items">--}}
{{--                        <a href="{{ route('info.slots', [], false) }}" class="footer__link content-link">{{ __('footer.slots') }}</a>--}}
{{--                        <a href="{{ route('info.table', [], false) }}" class="footer__link content-link">{{ __('footer.table') }}</a>--}}
{{--                        <a href="{{ route('info.live', [], false) }}" class="footer__link content-link">{{ __('footer.liveDealer') }}</a>--}}
{{--                        <a href="{{ route('info.jackpots', [], false) }}" class="footer__link content-link">{{ __('footer.jackpots') }}</a>--}}
{{--                    </div>--}}
{{--                </div>--}}

{{--                <div class="footer__links-column">--}}
{{--                    <div class="footer__links-title">{{ __('footer.information') }}</div>--}}
{{--                    <div class="footer__links-items">--}}
{{--                        <a href="{{ route('help.faq', [], false) }}" class="footer__link content-link">{{ __('footer.faq') }}</a>--}}
{{--                        <a href="{{ route('info.aboutUs', [], false) }}" class="footer__link content-link">{{ __('footer.aboutUs') }}</a>--}}
{{--                        <a href="{{ route('info.ofertPolicy', [], false) }}" class="footer__link content-link">{{ __('footer.rightsAndConditions') }}</a>--}}
{{--                        <a href="{{ route('info.bonuses', [], false) }}" class="footer__link content-link">{{ __('footer.aboutBonuses') }}</a>--}}
{{--                        <a href="{{ route('info.paymentPolicy', [], false) }}" class="footer__link content-link">{{ __('footer.payments') }}</a>--}}
{{--                        <a href="{{ route('info.amlPolicy', [], false) }}" class="footer__link content-link">{{ __('footer.amlAndKycPolitics') }}</a>--}}
{{--                        <a href="{{ route('info.responsibleGame', [], false) }}" class="footer__link content-link">{{ __('footer.responsiblePlaying') }}</a>--}}
{{--                        <a href="{{ route('info.affiliatesPolicy', [], false) }}" class="footer__link content-link">{{ __('footer.partnership') }}</a>--}}
{{--                    </div>--}}
{{--                </div>--}}
            </nav>
        </div>
    </div>
    <div class="bottom-menu__close-button">&times;</div>
</div>
