
<style>
    #tv-bet-frame {
        border: none;
        width: 100%;
        height: 100rem;
    }
</style>
<iframe src="{{ route('tvBet.frame', [], false) }}?gameId={{ request()->get('gameId', 1) }}" id="tv-bet-frame"></iframe>


