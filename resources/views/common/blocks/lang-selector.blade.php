@php
$url = request()->getRequestUri();
$a = explode('/', $url);
if (count($a) > 0) {
    array_shift($a);
    if ($a[0] === '{locale}') array_shift($a);
    if (count($a) > 0) {
        if (in_array($a[0], config('app.available_locales'))) {
            array_shift($a);
        }
    }
    $url = implode('/', $a);
}
$locales = \App\Helpers\Localization::getLocalNames();
@endphp

<div class="lang-selector">
    <div class="lang-selector__flag-wrap">
        <img src="/img/flags-wave/{{ \Illuminate\Support\Facades\App::getLocale() }}.png" data-lang="{{ \Illuminate\Support\Facades\App::getLocale() }}" class="lang-selector__flag">
    </div>

    <div class="lang-selector__value">{{ $locales[\Illuminate\Support\Facades\App::getLocale()] }}</div>

    <div class="arrow-down"></div>

    <div class="lang-selector__dropdown">
        @foreach($locales as $locale => $name)
            <a href="/{{$locale}}/{{ $url }}" class="lang-selector__option" data-lang="{{$locale}}">{{$name}}</a>
        @endforeach
    </div>
</div>
