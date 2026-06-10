window.pixelAction = async function(aid, uid) {
    let userId = 'n/a';
    if (uid !== 0) {
        userId = uid;
    }
    let url = `https://r.uuidksinc.net/match/${aid}/?remote_uid=${userId}`;
    fetch(url, {
        method: 'GET',
        credentials: 'include', //important for cookie sync
        headers: {
            'Content-Type': 'application/json'
        }
    })
}

document.addEventListener('userEvent', (e) => {
    // pixel events
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

    if (codes[e.detail.name] !== undefined) {
        window.callAction(e.detail.name);
        for (let cd of codes[e.detail.name]) {
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

document.addEventListener('userEvent', (e) => {
    // pixel events
    const codes = {
        'regfin': [1042332],
        'regnotfin': [1042331],
        'ftd': [1042330],
        'bets': [1042327, 1042333],
        'visit': [1042326],
        'dep2': [1042335],
        'dep3': [1042336],
        'dep4': [1042337],
        'deposit': [1042329, 1042334],
    };

    if (codes[e.detail.name] !== undefined) {
        for (let cd of codes[e.detail.name]) {
            window.pixelAction(cd, window.userId);
        }
    }
});
