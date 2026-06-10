<div class="page__head">
    <div class="n_container">
        <div class="page__block-go-back-to-mome">
            <img src="/images/svg/go-back-to-mome-icon.svg" alt="">
            <a href="{{route('landing', [], false)}}">{{__('slc.back_to_main')}}</a>
        </div>
        <h1 class="page__title flex">
            {{__('slc.my_items')}}
            <a href="{{route('slc.page-info', ['page' => 'my_items'], false)}}" class="page-info-icon ml-05" data-ajax="modal" data-modal-class="mobile-popup"></a>
        </h1>
    </div>
</div>
<main class="main" id="user-inventory">
    <div class="page__content">
        <div class="n_container">
            @if ($my_items->count() === 0)
                <div class="no-bonuses">
                    <div class="no-bonuses__block-image wrap-lazy-load-image">
                        <img src="/images/no-bonuses-image.png" alt="">
                    </div>
                    <h2>{{__('slc.oops_empty')}}</h2>
                    <div class="no-bonuses__block-text">
                        <p>{{__('slc.no_items')}}</p>
                    </div>
                    <div class="no-bonuses__links">
                        <a href="{{route('slc.tasks', [], false)}}" class="no-bonuses__link my-tasks">
                        <span class="no-bonuses__link-wrap-img">
                           <img src="/images/tasks-icon.png" alt="">
                        </span>
                            {{__('menu.daily_tasks')}}
                        </a>
                        <a href="achievements.html" class="no-bonuses__link achievements">
                        <span class="no-bonuses__link-wrap-img">
                           <img src="/images/achievements-icon.png" alt="">
                        </span>
                            {{__('menu.achievements')}}
                        </a>
                        <a href="{{route('slc.shop', [], false)}}" class="no-bonuses__link shop">
                        <span class="no-bonuses__link-wrap-img">
                           <img src="/images/shop-icon.png" alt="">
                        </span>
                            {{__('menu.shop')}}
                        </a>
                    </div>
                </div>
            @else
                <div class="shop__items">
                    @foreach($my_items as $my_item)
                        @if(is_array($my_item))
                            @foreach($my_item as $group_item)
                                {!! $group_item->getInfo()->renderInventoryItem($group_item) !!}
                                @break
                            @endforeach
                        @else
                            {!! $my_item->getInfo()->renderInventoryItem($my_item) !!}
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</main>
@include('phone.blocks.bottom-deposit-btn')
