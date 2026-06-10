(() => {
    document.addEventListener('touchstart', handleTouchStart);
    document.addEventListener('touchmove', handleTouchMove);

    let xDown = null;
    let yDown = null;
    let target = null;

    function handleTouchStart(e) {
        target = e.target;
        xDown = e.touches[0].clientX;
        yDown = e.touches[0].clientY;
    }

    function handleTouchMove(e) {
        if (!xDown || !yDown) return;


        let xUp = e.touches[0].clientX;
        let yUp = e.touches[0].clientY;

        let xDiff = xDown - xUp;
        let yDiff = yDown - yUp;

        if (Math.abs(xDiff) > Math.abs(yDiff)) {
            if (xDiff > 0) {
                dispatch('swipeLeft', Math.abs(xDiff));
            } else {
                dispatch('swipeRight', Math.abs(xDiff));
            }
        } else {
            if (yDiff > 0) {
                dispatch('swipeUp', Math.abs(yDiff));
            } else {
                dispatch('swipeDown', Math.abs(yDiff));
            }
        }

        xDown = null;
        yDown = null;
        target = null;
    }


    function dispatch(eventName, distance) {
        let t = target;
        while (t !== null) {
            if (t.className !== undefined && t.className !== '') {
                let c = t.className.split(" ");
                for (let i = 0; i < c.length; i++) {
                    let event = new CustomEvent(eventName + ':' + c[i], {detail: {
                        target: t,
                        distance: Math.abs(distance)
                    }});
                    document.dispatchEvent(event);
                }
            }
            t = t.parentNode;
        }
    }
})();

