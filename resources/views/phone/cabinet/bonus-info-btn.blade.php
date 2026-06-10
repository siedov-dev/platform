@if($bonus->title === 'bonus.first_dep_bonus')
    <a class="bonus-btn-info" href="{{route('bonus.fdep-info', [], false)}}" data-ajax="modal">
        <div>{{__('bonus.info')}}</div>
        <div>&times;</div>
    </a>
@else
    <a class="bonus-btn-info" href="{{route('bonus.popup-info', ['id' => $bonus->getId(), 'sub_id' => $bonus->getSubId()], false)}}" data-ajax="modal" data-popup-type="bottom-popup">
        <div>{{__('bonus.info')}}</div>
        <div>&times;</div>
    </a>
@endif
