<div class="user-call-request-block {{$state}}">
    @if($state === 'pending')
        <div class="check-icon">
            <img src="/img/ui/icons/call_request/check.svg">
        </div>
        <div class="pending-text">{{__('user-calls.pending-text')}}</div>
    @else
        <img src="/img/ui/icons/call_request/call.svg" alt="">
        <div class="title">{{__('user-calls.title')}}</div>
        <div class="subtitle">{{__('user-calls.subtitle')}}</div>
        <div class="button button_green action-user-call-request">{{__('user-calls.order')}}</div>
        @if($state === "disabled")
            <div class="confirm-info">
                <img src="/img/ui/icons/call_request/user-check.svg">
                <p>{{__('user-calls.confirm-title')}}</p>
            </div>
            <button class="button button_red text-red mt-1"
                    onclick="cabinetShowBlock('settings')">{{__('user-calls.confirm')}}</button>
        @endif
    @endif
</div>
