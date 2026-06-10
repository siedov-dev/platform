<form class="flex column ai-center no-click-hide self-lock-modal" action="{{route('cabinet.lock', [], false)}}" method="POST">
    @csrf
    <input type="hidden" name="days" value="{{$days}}">
    <img src="/img/ui/icons/red-hand.svg" alt="stop hand">
    <div class="modal-title mt-2 text-center">{{__('cabinet.lock-modal-title', ['days' => $days])}}</div>
    <div class="danger-pad mt-1 w-100">{{__('cabinet.lock-modal-warning', ['days' => $days])}}</div>
    <p class="mt-1">{{__('cabinet.lock-modal-recommends')}}</p>
    <div class="flex jc-sb mt-1 w-100">
        <div class="button button_blue grow mr-1" onclick="Modal.hide(this)">{{__('cabinet.lock-no')}}</div>
        <button type="submit" class="button button_danger grow">{{__('cabinet.lock-yes')}}</button>
    </div>
</form>
