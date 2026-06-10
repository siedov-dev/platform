@php
    $groups = \App\GamesProviders\GamesManager::getAllGroups();
@endphp
<div class="providers-list mb-4 games-slider">
    <div class="games-slider__header mb-1">
        <div class="games-slider__info">
            <div>
                <div class="games-slider__title">{{ __('landing.providers') }}</div>
            </div>
        </div>

        <div class="games-slider__controls">
            <div class="all-button games-slider__all-button">{{ __('landing.all') }}</div>
            <div class="games-slider__left slider__disabled"></div>
            <div class="games-slider__right"></div>
        </div>
    </div>

    <div id="landing__providers" class="landing__providers-list slider scroll-hidden">
        @foreach ($groups as $group)

            <div class="provider-games-wrapper slider__item">
                <div class="flex ai-center jc-sb">
                    <div class="providers-icon">
                        <img src="{{$group->getIconUrl()}}">
                        <div class="flex column ml-05">
                            <div class="name">{{$group->getName()}}</div>
                        </div>
                    </div>
                    <a class="all-button games-slider__all-button" href="/games?providers={{$group->getProviderId()}}%7C{{$group->getId()}}&category={{implode(',',$group->getAllCategories())}}">
                        {{ __('landing.all') }} ({{ $group->count() }})
                    </a>
                </div>

                <div class="group-games">
                    @php $i=0; @endphp
                    @foreach($group->getAllGames() as $id => $game)
                        @if($i > 3) @break @endif
                            @include('desktop.games.single-game', ['game' => $game])
                        @php $i++ @endphp
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

</div>
