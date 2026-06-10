@php
    use Illuminate\Support\Facades\Auth;
    /** @var $tournament \App\Models\Tournaments\GoTournament */
    $table = $tournament->getWinnersTable(10);
    $user_place = 0;
    // user position
    $user_id = Auth::id();
    foreach ($table as $place => $vals) {
        if ($vals['points'] > 0 && $vals['user_id'] == $user_id) {
            $user_place = $place;
            break;
        }
    }
@endphp
<div class="tournament__play-table" data-tournament-id="{{ $tournament->getId() }}">
    <div class="flex column">
        <div class="title">
            {{ $tournament->getName() }}
        </div>
        <div class="flex column ai-center mb-05">
            <div class="subtitle">{{ __('tournament.prize_fund') }}</div>
            <h2>{{ $tournament->getPrizeFund() }}</h2>
        </div>

        <table class="">
            <tbody>
            @php
                $table['user_position'] = $user_place;
                $page = request('page', 1);
                $table_size = 10;
                if ($table_size < 10) $table_size = 10;
            @endphp

            @for($i = 1; $i <= $table_size; $i++)
                @php
                    $t_index = $i + (($page-1) * $table_size);
                @endphp
                <tr class="@if($t_index === $table['user_position']) players_slot @endif" data-index="{{ $i }}">
                    <td data-user="{{$table[$t_index]['user_id']??''}}" data-index="{{ $i }}">{{$i}}</td>
                    <td>
                        {{(isset($table[$t_index]) && $table[$t_index]['points'] > 0)? \App\MUser::hideLogin($table[$t_index]['login']?? '-') : '-'}}
                    </td>
                    <td>
                        {{(($table[$t_index]['points']?? 0) > 0)? ($table[$t_index]['points']) : '-'}}
                    </td>
                </tr>
            @endfor
            </tbody>
        </table>
    </div>
</div>
