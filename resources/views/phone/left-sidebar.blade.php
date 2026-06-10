<div id="left-sidebar">
    <div class="left-sidebar__close-button" onclick="document.dispatchEvent(new CustomEvent('hideLeftSidebar'))">&times;</div>

    <div class="mt-3"></div>

    @include('common.menu')
</div>
<div id="left-sidebar-backdrop" class="sidebar-backdrop" onclick="document.dispatchEvent(new CustomEvent('hideLeftSidebar'))"></div>

<script>
    (() => {
        // Store references to layout elements.
        let sidebar = document.getElementById('left-sidebar');
        let backdrop = document.getElementById('left-sidebar-backdrop');

        // Animation parameters.
        let easing = t => t<.5 ? 2*t*t : -1+(4-2*t)*t;
        let duration = 250;

        // Menu showing.
        document.addEventListener('showLeftSidebar', () => {
            sidebar.style.opacity = '0';
            sidebar.style.left = '-32px';
            sidebar.style.display = 'flex';

            backdrop.style.opacity = '0';
            backdrop.style.display = 'block';
            document.body.style.overflowY = 'hidden';

            let start = Date.now();

            let f = () => {
                let k = (Date.now() - start) / duration;
                if (k > 1) k = 1;
                k = easing(k);

                sidebar.style.opacity = k.toString();
                sidebar.style.left = (-32 + 32*k) + 'px';
                backdrop.style.opacity = k.toString();

                if (k < 1) requestAnimationFrame(f);
            };
            requestAnimationFrame(f);
        });

        // Menu hiding.
        document.addEventListener('hideLeftSidebar', () => {
            let start = Date.now();
            document.body.style.overflowY = null;

            let f = () => {
                let k = (Date.now() - start) / duration;
                if (k > 1) k = 1;
                k = easing(k);

                sidebar.style.opacity = (1 - k).toString();
                sidebar.style.left = -32*k + 'px';
                backdrop.style.opacity = (1 - k).toString();

                if (k < 1) {
                    requestAnimationFrame(f);
                } else {
                    sidebar.style.display = 'none';
                    backdrop.style.display = 'none';
                }
            };
            requestAnimationFrame(f);
        });
    })();
</script>
