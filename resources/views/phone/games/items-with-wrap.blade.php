<div>
    <div class="games-slider__games">
        @foreach ($games as $game)
            @include('phone.games.single-game', ['game' => $game])
        @endforeach
    </div>

    <div id="lnd__show-more-wrap" class="show-more-wrap flex jc-center">
        @include('phone.games.tag-show-more', ['games' => $games, 'customClass' => 'lnd__show-more'])
    </div>
</div>

