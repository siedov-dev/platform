<style>
    #vpn-recomendation {
        display: flex;
        flex-direction: column;
        padding: 1rem;
        position: fixed;
        right: 1rem;
        bottom: 1rem;
        background-color: #fff;
        width: 22rem;
        border-radius: 0.25rem;
        z-index: 500;
        color: #0a0a17;
    }
    #vpn-recomendation span {
        font-size: 0.75rem;
        margin-bottom: 1rem;
    }
    @media (max-width: 600px) {
        #vpn-recomendation {
            bottom: 5rem;
        }
    }
</style>

<div id="vpn-recomendation" style="display: none">
    <span>{{ __('vpn.recommendation') }}</span>
    <div class="button button_green" onclick="this.parentNode.style.display = 'none';">{{ __('vpn.accept') }}</div>
</div>
