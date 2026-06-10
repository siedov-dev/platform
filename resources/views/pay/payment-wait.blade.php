<style>
    .white-pad{
        display: flex;
        flex-flow: column;
        justify-content: space-evenly;
        align-items: center;
        background-color: white;
        color: black;
        max-width: 400px;
        padding: 2rem;
    }
    .white-pad p{
        color: #3d3e6d;
        font-weight: 700;
        font-size: .875rem;
        text-indent: 1.5em;
        line-height: 1rem;
        text-align: justify;
    }
</style>
<div class="white-pad mt-2" id="wait-payment">
    <p class="flex w-100 jc-sb p-1 ml-2 mr-2">{{__('wallet.wait_pay_not_close')}}</p>
    <div class="flex jc-center">
        @include('common.blocks.loading-dots')
    </div>
    <div class="redirect-form" style="display: none"></div>
</div>
<script>
    (() => {
        let timer = setInterval(async () => {
            try {
                let resp = await network.post('{{action([\App\Http\Controllers\PayController::class, 'waitPayment'])}}', {transactionId: '{{$transaction->id}}'});
                let data = await resp.json();
                console.log(data);
                if (data.status !== {{\App\PayoutTransaction::STATUS_PENDING}}) {
                    clearInterval(timer);
                    let waitEl = document.querySelector('#wait-payment');
                    waitEl.firstElementChild.innerHTML = data.message;
                    // waitEl.lastElementChild.style.display = "none";
                    if (data.status === {{\App\PayoutTransaction::STATUS_3DS}} || data.status === {{\App\PayoutTransaction::STATUS_3DS_REDIRECT}}) {
                        waitEl.lastElementChild.innerHTML = data.form;
                        waitEl.lastElementChild.firstElementChild.submit();
                    }
                }
            } catch (e) {
                console.log(e.toString());
            }
        }, 4000);
    })();
</script>
