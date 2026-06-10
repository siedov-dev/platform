<?php

use App\GamesProviders\GamesManager;

if (!isset($categories)) $categories = [];

$selectedProviders = explode(',', request()->get('providers', ''));
if (reset($selectedProviders) === '') array_shift($selectedProviders);

$groups = GamesManager::getAllGroups();
?>

<form action="{{ route('games', [], false) }}" method="GET" id="games-search-form" class="games-search-form">

    <input type="hidden" name="categories" value="{{ request()->get('categories', '') }}">
    <input type="hidden" name="pageSize" value="{{ $pageSize?? 60 }}">
    <!-- Category -->
    <div class="custom-select single-select games-search-form__category" data-placeholder="Категория..." style="display: none">
        <div class="custom-select__icon-wrap"><img src="/img/ui/icons/filter.svg"></div>
        <div class="custom-select__value">{{ __('games.category') }}...</div>
        <div class="custom-select__counter" @if(count($categories) > 0) style="visibility: visible" @endif>{{ count($categories) }}</div>
        <div class="custom-select__arrow arrow-down"></div>
        <input type="hidden" name="category" value="{{ implode(',', $categories) }}">
        <div class="custom-select__dropdown custom-select__dropdown_single">
            @if(auth()->check())
                <div class="custom-select__option @if(in_array('bonus', $categories)) custom-select__option_selected @endif" data-value="bonus">{{ __('games.gamesForBonus') }}</div>
            @endif
            <div class="custom-select__option @if(in_array('slots', $categories)) custom-select__option_selected @endif" data-value="slots">{{ __('games.slots') }}</div>
            <div class="custom-select__option @if(in_array('table', $categories)) custom-select__option_selected @endif" data-value="table">{{ __('games.table') }}</div>
            <div class="custom-select__option @if(in_array('sport', $categories)) custom-select__option_selected @endif" data-value="sport">{{ __('games.sport') }}</div>
            <div class="custom-select__option @if(in_array('live', $categories)) custom-select__option_selected @endif" data-value="live">{{ __('games.live') }}</div>
            <div class="custom-select__option @if(in_array('crash', $categories)) custom-select__option_selected @endif" data-value="crash">{{ __('games.crash') }}</div>

        </div>
    </div>
    <div class="gradient-background">
        <label class="input__wrap games-search-form__name">
            <img src="/img/icons/search_white.svg" class="input__icon_left">
            <input type="text" name="name" class="input__field" placeholder="{{ __('games.findGame') }}..." value="{{request()->get('name', '')}}">
            <img src="/img/icons/clear.svg" class="input__icon_right" onmousedown="event.preventDefault();this.previousElementSibling.value='';">
        </label>
    </div>

    <!-- Provider -->
    <div class="custom-select games-search-form__provider ml-1" data-placeholder="{{ __('games.provider') }}..." data-dropdown="#games-search-form-providers">
{{--        <div class="custom-select__icon-wrap"><img src="/img/icons/dots.png"></div>--}}
        <div class="custom-select__value">{{ __('games.provider') }}...</div>
        <div class="custom-select__counter" @if(count($selectedProviders) > 0) style="visibility: visible" @endif>{{ count($selectedProviders) }}</div>
        <div class="arrow-position">
            <div class="custom-select__arrow arrow-down"></div>
        </div>
        <input type="hidden" name="providers" value="{{request()->get('providers', '')}}">
    </div>
</form>


<div id="games-search-form-providers" class="custom-select__dropdown single-select scrollable ">

    <div class="custom-select__dd-close-btn">&times;</div>
    <div class="custom-select__dd-head">
        <h3 class="custom-select__value">{{ __('games.provider') }}</h3>
        <div class="custom-select__counter">{{ count($selectedProviders) }}</div>
    </div>

    <label class="input__wrap games-search-form__provider-filter">
        <img src="/img/icons/Search.png" class="input__icon_left">
        <input type="text" name="groupFilter" class="input__field input__field_icon-left" placeholder="{{ __('games.findProvider') }}..." value="">
    </label>

    <div class="scrollable-content">
        @foreach($groups as $group)
            <div class="custom-select__option option-icon @if(in_array($group->getProviderId().'|'.$group->getId(), $selectedProviders)) custom-select__option_selected @endif"
                 data-value="{{$group->getProviderId()}}|{{$group->getId()}}" data-name="{{$group->getName()}}">
                <a class="providers-icon" href="/games?providers={{$group->getProviderId()}}%7C{{$group->getId()}}&category={{implode(',',$group->getAllCategories())}}">
                    {{--                        <img src="/img/icons/providers/{{$group->getProviderId()}}/{{str_replace(' ', '_', $group->getName())}}.png">--}}
                    <img src="{{$group->getIconUrl()}}">
                    <div class="flex column ml-05">
                        <div class="name">{{$group->getName()}}</div>
                        <div class="count">{{$group->count()}}&nbsp;игры</div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
{{--    <div class="flex jc-sb mb-1 mt-1">--}}
{{--        <button class="button button_outlined" id="providers-reset-filter" onclick="this.parentElement.parentElement.querySelectorAll('.custom-select__option.custom-select__option_selected').forEach(e => e.click())">{{__('games.reset')}}</button>--}}
{{--        <button class="button button_green" onclick="this.parentElement.parentElement.querySelector('.custom-select__dd-close-btn').click()">{{__('games.apply')}}</button>--}}
{{--    </div>--}}
</div>


<script>
    (() => {
        let form = document.getElementById('games-search-form');
        let providerSelect = form.querySelector('.games-search-form__provider');
        let categorySelect = form.querySelector('.games-search-form__category');
        let input = form.querySelector('.games-search-form__name');
        let providerFilter = document.querySelector('.games-search-form__provider-filter input');

        input.addEventListener('input', () => {
            document.dispatchEvent(new CustomEvent('games:searchWithDelay'));
        });

        providerFilter.addEventListener('input', (e) => {
            let inputEl = e.srcElement;
            let filterGroup = inputEl.value.toLowerCase();
            inputEl.closest('#games-search-form-providers').querySelectorAll('.scrollable-content .custom-select__option').forEach(el => {
                    if (el.getAttribute('data-name').toLowerCase().match(filterGroup)) {
                        el.classList.remove('hidden');
                    } else {
                        el.classList.add('hidden');
                    }
                }
            );
        });

        providerSelect.addEventListener('change', () => {
            document.dispatchEvent(new CustomEvent('games:search'));
            let indicator = document.querySelector('.games-search-form__provider .custom-select__indicator');
            if (indicator !== null) {
                let options = document.querySelectorAll('#games-search-form-providers .custom-select__option_selected');
                indicator.textContent = options.length;
                indicator.style.display = options.length > 0 ? 'flex' : 'none';
            }
            setTimeout(() => document.dispatchEvent(new CustomEvent('closeActiveSelect')) ,0);
        });

        categorySelect.addEventListener('change', () => {
            document.dispatchEvent(new CustomEvent('games:search'));
        });

        form.addEventListener('submit', e => {
            e.preventDefault();
            document.dispatchEvent(new CustomEvent('games:search'));
        });
    })();
</script>

