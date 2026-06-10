@php
    /** @var \App\Models\Promos\PromoItem $item */
@endphp
<div class="page__head slc-shop">
    <div class="n_container">
        <div class="page__block-go-back-to-mome">
            <img src="/images/svg/go-back-to-mome-icon.svg" alt="">
            <a href="{{route('landing')}}" class="content-link">{{__('slc.back_to_main')}}</a>
        </div>
        <div class="page__head-wrap-title">
            <h1 class="page__title flex">
                {{__('promos.promos')}}
                <a href="{{route('slc.page-info', ['page' => 'promos'], false)}}" class="page-info-icon ml-05" data-ajax="modal"></a>
            </h1>
        </div>
    </div>
</div>
<main class="main">
    <div class="page__content">
        <div class="n_container">
            <div class="promo__items">
                @foreach($items as $item)
                    @include('desktop.promos.promo', ['item' => $item])
                @endforeach
            </div>
        </div>
    </div>
</main>
