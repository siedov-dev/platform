(() => {
    window.Toast = {
        async show(msg, ttl) {
            let toast = document.createElement('div');
            toast.className = 'toast';
            toast.textContent = msg;
            toast.style.right = '1rem';
            toast.style.top = '1rem';
            toast.style.opacity = '0';
            document.body.appendChild(toast);

            let anim = new Anim(250);
            await anim.start(k => toast.style.opacity = k.toString());
            setTimeout(async () => {
                await anim.start(k => {
                    toast.style.opacity = (1 - k).toFixed(2)
                });
                document.body.removeChild(toast);
            }, ttl || 1000);
        }
    }
})();
