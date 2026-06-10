<style>
    .bonus-not-support{
        display: flex;
        flex-flow: column;
        justify-content: space-evenly;
        align-items: center;
        background-color: white;
        color: black;
        max-width: 400px;
        padding: 2rem;
        border-radius: 1rem;
    }
    .bonus-not-support img{
        border-radius: 1rem;
    }
    .bonus-not-support p{
        color: #c60109;
        font-weight: 700;
        font-size: .875rem;
        text-indent: 1.5em;
        line-height: 1.2rem;
        text-align: center;
        text-transform: uppercase;
    }
</style>
<div class="bonus-not-support" id="demo-not-allowed">
    <img src="{{$game->getImageUrl()}}" class="mb-2" width="190px" height="256px" style="object-fit: cover;">
    <p>{{__('games.demo_not_allowed')}}</p>
</div>
    <script>
        setTimeout(async function () {
            Modal.hide('.modal .content');
            @if(!\Illuminate\Support\Facades\Auth::check())
                let regButton = document.getElementById('register-button');
                    regButton.click();
            @endif
        }, 3000);
    </script>
