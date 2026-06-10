@if(isset($_GET['bonusId']) && auth()->check())
    @php($bonus = \App\Models\Bonuses\UserBonus::where('id', $_GET['bonusId'])->first())
    @if ($bonus !== null)
        @php($bonus = \App\Models\Bonuses\UserBonus::convertByType($bonus))
        <div class="bonus-modal-hint">
            {{ $bonus->getDescription() }}
        </div>
    @endif
@endif
