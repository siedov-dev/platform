<div class="right-sidebar-wrap">
    <div id="right-sidebar" class="p-05">
        <div class="right-sidebar__close-button" onclick="document.dispatchEvent(new CustomEvent('hideRightSidebar'))">&times;</div>

        @if(auth()->check())
            <div class="sidebar-user-info mt-1" style="padding-top: 1rem;">

                <!-- Avatar -->
            @include('common.blocks.user-avatar', ['percent' => 0])

            <!-- Name and rank -->
                <div class="user-menu__texts">
                    <div class="user-menu__name">{{ auth()->check() ? auth()->user()->login : 'Guest' }}</div>
                    @if(\Illuminate\Support\Facades\Auth::check())
                        <div class="user-menu__id">id-{{App\MUser::getAuth()?->getId()}}</div>
                    @endif
{{--                    <div>--}}
{{--                        <span class="user-menu__rank-name">{{ \App\MUser::getAuth()?->getVipStatus()?->statusInfo?->getName() }}</span>&nbsp;--}}
{{--                        <span class="user-menu__rank-progress">{{\App\MUser::getAuth()->getNextRankProgress()}}%</span>--}}
{{--                    </div>--}}
                </div>
            </div>

{{--            @include('phone.blocks.wallet-selector')--}}
            @include('desktop.blocks.wallet-selector')
            <div id="bonus-lane"></div>
        @else
            <div class="mt-3"></div>
        @endif

        @include('phone.blocks.user-menu')
    </div>
    <div id="right-sidebar-backdrop" class="sidebar-backdrop" onclick="document.dispatchEvent(new CustomEvent('hideRightSidebar'))"></div>

    <script>
        (() => {
            // Store references to layout elements.
            let sidebar = document.getElementById('right-sidebar');
            let backdrop = document.getElementById('right-sidebar-backdrop');

            // Animation parameters.
            let easing = t => t<.5 ? 2*t*t : -1+(4-2*t)*t;
            let duration = 250;

            let bonusChecked = false;

            // Menu showing.
            document.addEventListener('showRightSidebar', () => {
                sidebar.style.opacity = '0';
                sidebar.style.right = '-32px';
                sidebar.style.display = 'flex';

                backdrop.style.opacity = '0';
                backdrop.style.display = 'block';
                document.body.style.overflowY = 'hidden';

                if (bonusChecked !== true) {
                    bonusChecked = true;
                    loadToElm('{{route('bonus.count', [], false)}}', '#bonus-av-cnt');
                    loadToElm('{{route('bonus.lane', [], false)}}', '#bonus-lane');
                    setTimeout(() => {
                        bonusChecked = false
                    }, 3000)
                    loadToElm('{{route('bonus.lane', [], false)}}', '#bonus-lane');
                }

                let start = Date.now();

                let f = () => {
                    let k = (Date.now() - start) / duration;
                    if (k > 1) k = 1;
                    k = easing(k);

                    sidebar.style.opacity = k.toString();
                    sidebar.style.right = (-32 + 32*k) + 'px';
                    backdrop.style.opacity = k.toString();

                    if (k < 1) requestAnimationFrame(f);
                };
                requestAnimationFrame(f);
            });

            // Menu hiding.
            document.addEventListener('hideRightSidebar', () => {
                let start = Date.now();
                document.body.style.overflowY = null;

                let f = () => {
                    let k = (Date.now() - start) / duration;
                    if (k > 1) k = 1;
                    k = easing(k);

                    sidebar.style.opacity = (1 - k).toString();
                    sidebar.style.right = -32*k + 'px';
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
</div>
