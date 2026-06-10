<style>
    #cookies-policy {
        display: flex;
        flex-direction: column;
        padding: 1rem;
        position: fixed;
        right: 1rem;
        bottom: 1rem;
        background-color: #fff;
        width: 22rem;
        border-radius: 0.25rem;
        z-index: 600;
        color: #0a0a17;
    }
    #cookies-policy span {
        font-size: 0.75rem;
        margin-bottom: 1rem;
    }
    @media screen and (max-width: 800px)  {
        #cookies-policy {
            right: auto;
            left: 50%;
            transform: translateX(-50%);
        }
    }
</style>

<div id="cookies-policy" style="display: none">
    <span>{{ __('policy.cookiesPolicy') }}</span>
    <div class="button button_green" onclick="setCookie('cookiesPolicy', '1'); this.parentNode.style.display = 'none';">{{ __('policy.accept') }}</div>
</div>

<script>
    (() => {
        if (getCookie('cookiesPolicy') === undefined) {
            setTimeout(() => {
                // let expires = new Date();
                // expires.setTime(expires.getTime() + 30 * 1000); // 30 seconds
                // setCookie('cookiesPolicy', '1', {'expires':expires});
                setCookie('cookiesPolicy', '1');
                document.getElementById('cookies-policy').style.display = 'flex';
            }, 1000);
        }
    })();
</script>
