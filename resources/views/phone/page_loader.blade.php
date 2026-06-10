<script>
    (() => {
        document.addEventListener('DOMContentLoaded', async () => {
            let href = '{!! $url !!}';
            if (window.preloaderProgress < 20) window.preloaderProgress = 25;
            let res = await network.get(href, true);
            let html = await res.text();

            let observer = new MutationObserver(() => {
                document.dispatchEvent(new CustomEvent("layout-changed"));
            });
            observer.observe(document.getElementById('layout-content'), {
                childList: true,
                subtree: false
            });

            setInnerHtml(document.getElementById('layout-content'), html);

            document.addEventListener('layout-changed', () => {
                console.log('layout-changed');
                if (window.preloaderProgress < 95) window.preloaderProgress = 100;
                completePreloader();
            });

            // setTimeout(() => {
            //     if (window.preloaderProgress < 80) window.preloaderProgress = 85;
            // }, 1000);
        });
    })()
</script>
