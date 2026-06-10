
function drawCircle(color, lineWidth, percent, ctx, radius, fill =  false) {
    percent = Math.min(Math.max(0, percent || 1), 1);
    ctx.beginPath();
    ctx.arc(0, 0, radius, 0, Math.PI * 2 * percent, false);
    ctx.strokeStyle = color;
    ctx.lineCap = 'round'; // butt, round or square
    ctx.lineWidth = lineWidth;
    if(fill === true) {
        ctx.fill();
    } else {
        ctx.stroke();
    }
}


// http://jsfiddle.net/9zwdvq21/
for(let el of document.querySelectorAll(".circle-progress")){

    let options = {
        percent:  el.getAttribute('data-percent') || 25,
        size: el.getAttribute('data-size') || 100, //radius
        lineWidth: el.getAttribute('data-line') || 5, //line width
        rotate: el.getAttribute('data-rotate') || 0,
        text: el.getAttribute('data-text') || '',
        fill: el.getAttribute('data-fill') || 'false'
    };

    let canvas = document.createElement('canvas');
    let span = document.createElement('span');
    span.textContent = options.text;
    span.style.width = 80+"px";
    span.style.padding = 10+"px";
    span.style.top = 25+"px";
    span.style.left = 0+"px";

    console.log(span.style);
    if (typeof(G_vmlCanvasManager) !== 'undefined') {
        G_vmlCanvasManager.initElement(canvas);
    }

    let ctx = canvas.getContext('2d');
    canvas.width = canvas.height = options.size;

    el.appendChild(span);
    el.appendChild(canvas);

    ctx.translate(options.size / 2, options.size / 2); // change center
    ctx.rotate((-1 / 2 + options.rotate / 180) * Math.PI); // rotate -90 deg

    let radius = (options.size - options.lineWidth) / 2;

    if(options.fill === "true") {
        drawCircle('#000', options.lineWidth, 100 / 100, ctx, radius, true);
    }
    drawCircle('#555555', options.lineWidth, 100 / 100, ctx, radius, false);
    if(options.percent > 0) {
        drawCircle('#78E545', options.lineWidth, options.percent / 100, ctx, radius, false);
    }
}
