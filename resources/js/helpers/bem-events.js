document.addEventListener('click', e => {
   let t = e.target;
   if (typeof t.className === 'string' && t.className.indexOf('webim') >= 0) {
       // console.log('Skip bem events');
       return;
   }

    while (t !== null) {
        if (typeof t.className === 'string' && t.className !== '') {
            let c = t.className.split(" ");
            for (let i = 0; i < c.length; i++) {
                let event = new CustomEvent('click:' + c[i], {detail: {
                    target: t,
                    originalEvent: e
                }});
                document.dispatchEvent(event);
            }
        }
        t = t.parentNode;
    }
});
