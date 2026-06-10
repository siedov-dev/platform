<style>

</style>
<div class="ranks">
    <div class="ranks-wrapper scroll-hidden">
        @foreach($ranks as $rank)
            @include('desktop.slc.achievements.ranks.rank', [
                    'rank_level' => $rank->getLevel(),
                    'selected' => $rank->getLevel() === $current_level,
                    'rank' => $rank,
                    'currency' => $user->getCurrency(),
                    ])
        @endforeach
    </div>
    <div class="scroll-control left prev-rank-button">
        <div class="arrow-left"></div>
    </div>
    <div class="scroll-control right next-rank-button">
        <div class="arrow-right"></div>
    </div>
</div>
