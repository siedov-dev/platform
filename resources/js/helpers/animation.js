
let easingFunctions = {
    // No easing, no acceleration
    linear: t => t,

    // Accelerating from zero velocity
    easeInQuad: t => t*t,

    // Decelerating to zero velocity
    easeOutQuad: t => t*(2-t),

    // Acceleration until halfway, then deceleration
    easeInOutQuad: t => t<.5 ? 2*t*t : -1+(4-2*t)*t,

    // Accelerating from zero velocity
    easeInCubic: t => t*t*t,

    // Decelerating to zero velocity
    easeOutCubic: t => (--t)*t*t+1,

    // Acceleration until halfway, then deceleration
    easeInOutCubic: t => t<.5 ? 4*t*t*t : (t-1)*(2*t-2)*(2*t-2)+1,

    // Accelerating from zero velocity
    easeInQuart: t => t*t*t*t,

    // Decelerating to zero velocity
    easeOutQuart: t => 1-(--t)*t*t*t,

    // Acceleration until halfway, then deceleration
    easeInOutQuart: t => t<.5 ? 8*t*t*t*t : 1-8*(--t)*t*t*t,

    // Accelerating from zero velocity
    easeInQuint: t => t*t*t*t*t,

    // Decelerating to zero velocity
    easeOutQuint: t => 1+(--t)*t*t*t*t,

    // Acceleration until halfway, then deceleration
    easeInOutQuint: t => t<.5 ? 16*t*t*t*t*t : 1+16*(--t)*t*t*t*t,

    // https://easings.net/#easeOutBack
    easeOutBack: t => {
        const c1 = 1.70158;
        const c3 = c1 + 1;
        return 1 + c3 * Math.pow(t - 1, 3) + c1 * Math.pow(t - 1, 2);
    },

    // https://easings.net/#easeInBack
    easeInBack: t => {
        const c1 = 1.70158;
        const c3 = c1 + 1;
        return c3 * t * t * t - c1 * t * t;
    },

    // https://easings.net/#easeInOutBack
    easeInOutBack: t => {
        const c1 = 1.70158;
        const c2 = c1 * 1.525;
        return t < 0.5
            ? (Math.pow(2 * t, 2) * ((c2 + 1) * 2 * t - c2)) / 2
            : (Math.pow(2 * t - 2, 2) * ((c2 + 1) * (t * 2 - 2) + c2) + 2) / 2;
    }
};

class Anim {
    constructor(duration, easing) {
        if (easing === undefined) easing = 'easeInOutQuad'
        this.easing = easingFunctions.hasOwnProperty(easing)
            ? easingFunctions[easing]
            : easingFunctions.linear;

        this.duration = duration;
        this.isStarted = false;
        this._id = 0;
    }

    start(callback) {
        this.isStarted = true;
        let id = window.performance.now()
        this._id = id;
        return new Promise(resolve => {
            let start = Date.now();
            let f = () => {
                if (!this.isStarted || this._id !== id) {
                    resolve();
                    return;
                }
                let k = (Date.now() - start) / this.duration;
                if (k > 1) k = 1;
                callback(this.easing(k));
                if (k < 1) {
                    requestAnimationFrame(f);
                } else {
                    resolve();
                }
            };
            requestAnimationFrame(f);
        });
    }

    stop() {
        this.isStarted = false;
        return this;
    }
}

function delay(time) {
    return new Promise(resolve => setTimeout(resolve, time));
}
