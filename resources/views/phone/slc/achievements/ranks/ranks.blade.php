<style>

</style>
<div class="ranks">
    <div class="ranks-wrapper scroll-hidden" onscroll="checkRanksScroll(this)">
        @foreach($ranks as $rank)
            @include('phone.slc.achievements.ranks.rank', [
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
