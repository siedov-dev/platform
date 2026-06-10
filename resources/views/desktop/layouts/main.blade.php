@php
/**
 * @var \App\Helpers\MetaTags $metaTags
 * @var string $content
 */
$user = \App\MUser::getAuth();
$uid = \Illuminate\Support\Facades\Auth::id();
$visited = session('first_visit', false);
session()->put('first_visit', true);
@endphp
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8"/>
    <meta name="referrer" content="strict-origin-when-cross-origin">
    <title>{{ $metaTags->title }}</title>

    <script src="{{mix('/js/preloader.js')}}"></script>

    <script>
        window.userId = {{ auth()->check() ? auth()->id() : 'null' }};
        window.userLocale = "{{ app()->getLocale() }}";
        window.userLocaleUrl = "{{ '/'.app()->getLocale() }}";
        window.server_time = "{{now()->format('Y-m-d H:i:s')}}";
        window.local_load_time = new Date().getTime();
    </script>

    @if(config('app.mode') !== "dev")
        <!-- Google Tag Manager -->
        <script async>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
                    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
                j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
                'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
            })(window,document,'script','dataLayer','GTM-W6GXRRV');</script>
        <!-- End Google Tag Manager -->

        <!-- Meta Pixel Code -->
{{--        <script>--}}
{{--            !function(f,b,e,v,n,t,s)--}}
{{--                {if(f.fbq)return;n=f.fbq=function(){n.callMethod?--}}
{{--                 n.callMethod.apply(n,arguments):n.queue.push(arguments)};--}}
{{--                 if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';--}}
{{--                 n.queue=[];t=b.createElement(e);t.async=!0;--}}
{{--                 t.src=v;s=b.getElementsByTagName(e)[0];--}}
{{--                 s.parentNode.insertBefore(t,s)}(window, document,'script',--}}
{{--                'https://connect.facebook.net/en_US/fbevents.js');--}}
{{--                fbq('init', '586528279368252');--}}
{{--                 fbq('track', 'PageView');--}}
{{--        </script>--}}
{{--            <noscript><img height="1" width="1" style="display:none"--}}
{{--                src="https://www.facebook.com/tr?id=586528279368252&ev=PageView&noscript=1"--}}
{{--            /></noscript>--}}
        <!-- End Meta Pixel Code -->
    @endif
    <link href="{{ mix('/css/desktop.css') }}" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
{{--    <link rel="manifest" href="{{asset('manifest.json')}}">--}}
    <link rel="manifest" href="{{parse_url(asset('manifest.json'), PHP_URL_PATH)}}">
    <meta name="csrf" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('js-localization.'. \Illuminate\Support\Facades\App::getLocale() .'.localization')
    <link href="{{ mix('/css/slc-pages.css') }}" rel="stylesheet">
    <script async src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>

    @include('desktop.layouts.customerio')

</head>
<body class="{{$_COOKIE['theme']??'dark'}} chat-hidden">

@include('common.blocks.preloader')
@include('common.blocks.short-message')
<script async>showPreloader(0, 2, false);</script>

@include('common.blocks.change-login-form')

<div id="layout-wrap">

    @include('desktop.header')

    <div id="layout-content-wrap">
        <div id="layout-content">
            <?= $content ?>
        </div>

        <div id="layout-footer-wrap">
            @include('desktop.footer_links')
        </div>
    </div>

    <div id="layout-overlay">
        <div id="layout-overlay-head" class="width-limiter">
            <div id="layout-overlay-title"></div>
            <div id="layout-overlay-close-button">
                {{ __('cabinet.close') }}
                <div id="layout-overlay-close-icon">&times;</div>
            </div>
        </div>
        <div id="layout-overlay-content-wrap">
            <div id="layout-overlay-content" class="width-limiter">
            </div>
        </div>
    </div>

</div>

    <script src="{{ mix('/js/desktop.js') }}"></script>

    @if(request()->route()->getName() === 'setNewPassword')
        @include('common.blocks.auth.new-password-form')
    @endif

    @if(!empty(\App\Helpers\Popup::message()))
        <script>
            Modal.show('<div class="layout-message"><div class="layout-message__text">{{ \App\Helpers\Popup::message() }}</div><div class="layout-message__button" onclick="Modal.hide(this)">OK</div></div>')
        </script>
    @endif


{{--    @if(auth()->check() && auth()->user()->isBlocked())--}}
{{--        <script>--}}
{{--            Modal.show('<div class="layout-message"><div class="layout-message__text">Ваш аккаунт заблокирован:<br/>{{ auth()->user()->block_reason }}</div><div class="layout-message__button" onclick="Modal.hide(this)">OK</div></div>')--}}
{{--        </script>--}}
{{--    @endif--}}


    @if(!\Illuminate\Support\Facades\Auth::check())
        <script>
            (async () => {
                let clickTime = Date.now();
                let res;
                @if (request()->route()->getName() === 'login')
                    res = await fetch('{!! route('login.form', ['login_error' => session('login_error'), 'email' => session('email', ''),'phone' => session('phone', ''),], false) !!}', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                });

                if (res.ok) {
                    let html = await res.text();
                    let content = document.querySelector('.login-form');
                    if (content === null) {
                        Modal.show(html);
                    } else {
                        setInnerHtml(content.parentNode, html);
                    }
                }
                @elseif (request()->route()->getName() === 'register' || request()->route()->getName() === 'register.phone')
                    res = await fetch('{{route('register.form', [], false)}}', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (res.ok) {
                    let html = await res.text();
                    let content = document.querySelector('.register-form__wrap');
                    if (content === null) {
                        Modal.show(html);
                    } else {
                        setInnerHtml(content.parentNode, html);
                    }
                }
                @endif
            })();
        </script>
    @endif


{{--    @include('desktop.layouts.webim')--}}
    @include('common.blocks.support-float')

    <style>
        .webim-html-button-container{
            bottom: 45px !important;
        }
    </style>

    @if(config('app.mode') !== "dev")
        @include('desktop.layouts.talkme')

        <!-- Global site tag (gtag.js) - Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=UA-208596125-1">
        </script>
        <script>
             window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', 'UA-208596125-1');
        </script>

{{--        <script src="https://cdn.onesignal.com/sdks/OneSignalSDK.js" async=""></script>--}}
{{--        <script>--}}
{{--            window.OneSignal = window.OneSignal || [];--}}
{{--            OneSignal.push(function() {--}}
{{--                OneSignal.init({--}}
{{--                    appId: "ef72e5dd-702f-42d2-8923-df64550efdfd",--}}
{{--                });--}}
{{--            });--}}
{{--        </script>--}}


{{--        <!-- Hotjar Tracking Code for https://krakencassino.com -->--}}
{{--            <script async>--}}
{{--                (async function(h,o,t,j,a,r){--}}
{{--                    h.hj=h.hj||function(){(h.hj.q=h.hj.q||[]).push(arguments)};--}}
{{--                    h._hjSettings={hjid:2596205,hjsv:6};--}}
{{--                    a=o.getElementsByTagName('head')[0];--}}
{{--                    r=o.createElement('script');r.async=1;--}}
{{--                    r.src=t+h._hjSettings.hjid+j+h._hjSettings.hjsv;--}}
{{--                    a.appendChild(r);--}}
{{--                })(window,document,'https://static.hotjar.com/c/hotjar-','.js?sv=');--}}
{{--            </script>--}}
    @endif
    @if(session('showDeposit') === true)
        <script>
            let event = new CustomEvent('click:wallet-selector__plus-button');
            document.dispatchEvent(event);
        </script>
    @endif
    @if(session('showPromo') === true)
        <script>
            document.addEventListener('DOMContentLoaded', ()=> {
                cabinetShowBonuses(true, true);
            })
        </script>
    @endif

    @if(session('cabinetShow') !== null)
        <script>
            document.addEventListener('DOMContentLoaded', ()=> {
                cabinetShowBlock("{{session('cabinetShow')}}", true);
            })
            completePreloader();
        </script>
    @endif
    @if(session('justLoggedIn') !== null)
        <script>
            localStorage.setItem('userId', '{{session('justLoggedIn')}}');
        </script>
    @endif
    @if(session('justRegistered') !== null)
        <script>
            document.addEventListener('DOMContentLoaded', ()=> {
                PopupModal.checkRegPopup();
            })
        </script>
    @endif
    @if(\Illuminate\Support\Facades\Auth::guest())
        <script>
                PopupModal.scheduleAuthPopup();
        </script>
    @endif

{{--    @include('common.cookies-policy')--}}
{{--    @include('common.vpn-recomendation')--}}
    <div id="bonus-info-modal" style="display: none; pointer-events: none">
    </div>
    @include('common.activate-promocode')
    @include('desktop.blocks.popup-modal')

    @if(!\Illuminate\Support\Facades\Cookie::get('fingerprint'))
        <script>
            generateFingerprint().then((fingerprint) => {
                setCookie('fingerprint', fingerprint.hash, {'max-age':3600})
            });
        </script>
    @endif

    <!-- Google Tag Manager (noscript) -->
        <noscript>
            <iframe src="https://www.googletagmanager.com/ns.html?id=GTM-W6GXRRV" height="0" width="0" style="display:none;visibility:hidden"></iframe>
        </noscript>
    <!-- End Google Tag Manager (noscript) -->

    @include('desktop.layouts.pixel')
    @if(\Illuminate\Support\Facades\Auth::check())
        @include('desktop.layouts.websocket')
    @endif

    @if(!$visited)
        <script>
            let event = new CustomEvent('userEvent', {detail: {
                    name: "visit",
                    payload: "null",
                }});
            document.dispatchEvent(event);
        </script>
    @endif
    </body>
</html>
