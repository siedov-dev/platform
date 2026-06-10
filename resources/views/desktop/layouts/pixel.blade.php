@php
    $uid = \Illuminate\Support\Facades\Auth::id();
@endphp
<script>
    (function(w, d, c) {
        var aid=1768973959,acl={'regfin':'1&extID={{$uid}}','ftd':'2&extID={{$uid}}','bets':'23&extID={{$uid}}',
            'deposit':'4&extID={{$uid}}','visit':'5&extID={{$uid}}','dep2':'6&extID={{$uid}}','dep3':'7&extID={{$uid}}',
            'dep4':'8&extID={{$uid}}','regnotfin':'14&extID={{$uid}}'};
        w.AU=w.AU||{};w.AU.aid=aid;w.AU.acl=acl;
        var a=d.createElement('script');a.onload=c;
        a.src='https://cdn.dspretargettraff.com/libs/a.js';
        d.body.appendChild(a);
    })(window, document, () => {
        document.dispatchEvent(new CustomEvent('pixel-api-loaded'))
    });
    window.callAction = function(a) {
        try{
            window.AU.action(a)
        }catch(e){
            console.error('Error calling action:',e.message||e)
        }
    };
</script>
