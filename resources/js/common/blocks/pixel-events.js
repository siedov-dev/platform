document.addEventListener('userEvent', (e) => {
    const codes = {
        'regfin': [69,70],
        'regnotfin': [68],
        'ftd': [71],
        'bets': [75, 77],
        'visit': [67],
        'dep2': [72],
        'dep3': [73],
        'dep4': [74],
        'deposit': [76],
    };

    if (codes[e.detail.data] !== undefined) {
        window.callAction(e.detail.data);
        for (let cd of codes[e.detail.data]) {
            (async function(w,d,a) {
                let wn=w.navigator,ws=window.screen,z="WEBGL";
                try{hc=wn.hardwareConcurrency}catch(p){hc=0};
                try{
                    let ca=d.createElement("canvas"),gl=ca.getContext("experimental-"+z.toLowerCase());
                    vc=escape(gl.getParameter(gl.getExtension(z+"_debug_renderer_info")["UNMASKED_RENDERER_"+z]))
                }catch(p){};
                p={sr:ws.width+"x"+ws.height,tz:-(new Date).getTimezoneOffset(),pl:wn.plugins&&wn.plugins.length,mi:wn.mimeTypes&&wn.mimeTypes.length,me:wn.deviceMemory,hc:hc,vc:vc};
                q=`https://rtb.dspretargettraff.com/au?a=${a}`;for(k in p)if((v=p[k])&&v!="undefined"){q+="&"+k+"="+v};
                (new Image).src=q;
            })(window,document,cd);
        }
    }
});
