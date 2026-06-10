<!-- Begin Talk-Me {literal} -->
<script async>
    (function(){(function c(d,w,m,i) {
        window.supportAPIMethod = m;
        var s = d.createElement('script');
        s.id = 'supportScript';
        var id = '19e97b7d2b1c4986d68df13f31ad5e55';
        s.src = (!i ? 'https://lcab.talk-me.ru/support/support.js' : 'https://static.site-chat.me/support/support.int.js') + '?h=' + id;
        s.onerror = i ? undefined : function(){c(d,w,m,true)};
         w[m] = w[m] ? w[m] : function(){(w[m].q = w[m].q ? w[m].q : []).push(arguments);};
        (d.head ? d.head : d.body).appendChild(s);
    })(document,window,'TalkMe')})();

    @if(app()->getLocale() === 'en' || app()->getLocale() === 'hi')
        window.TalkMeSetup = {
        language: "en"
    };
    @endif
</script>
<!-- {/literal} End Talk-Me -->
