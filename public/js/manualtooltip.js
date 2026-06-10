function show_mtooltip(name) {
    let el = document.querySelector('.manual-tooltip[data-tooltip-name="' + name + '"]');
    if(el) {
        if (getCookie(el.getAttribute('data-tooltip-name')) !== 'clicked') {
            document.querySelector('.manual-tooltip[data-tooltip-name="' + name + '"]').classList.add('tooltip-shown');
        } else {
            if (el.getAttribute('data-next-tooltip')) {
                show_mtooltip(el.getAttribute('data-next-tooltip'));
                el.removeAttribute('data-next-tooltip');
            }
        }
    }
}

(
    () => {
        for(let tooltipBody of document.querySelectorAll('.manual-tooltip')){
            tooltipBody.addEventListener('click', function (e) {
                this.classList.remove('tooltip-shown');
                setCookie(this.getAttribute('data-tooltip-name'), 'clicked', {'max-age':2147483647});
                if(this.getAttribute('data-next-tooltip')){
                    show_mtooltip(this.getAttribute('data-next-tooltip'));
                    // document.querySelector('.manual-tooltip[data-tooltip-name="'+this.getAttribute('data-next-tooltip')+'"]').classList.add('tooltip-shown');
                    // this.removeAttribute('data-next-tooltip');
                }
            })
        }

        document.addEventListener('DOMContentLoaded', function () {
            show_mtooltip("tooltip-cabinet");
        })
    }
)();
