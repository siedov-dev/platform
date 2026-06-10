<canvas id="wheelArrowCanvas" width="400" height="400"></canvas>

<script>
    (() => {
        // Get the canvas element
        let canvas = document.getElementById('wheelArrowCanvas');
        let context = canvas.getContext('2d');

        // Define the wheel properties
        let wheelRadius = canvas.width / 2;
        let wheelCenterX = canvas.width / 2;
        let wheelCenterY = canvas.height / 2;
        let numSections = {{ $sections?? 8 }};
        let sectionSize = (2 * Math.PI) / numSections;

        context.translate(wheelRadius, wheelRadius);
        context.rotate(-sectionSize / 2 - (Math.PI / 2) );
        context.translate(-wheelRadius, -wheelRadius);

        // Draw the wheel sections
        context.beginPath();
        context.lineWidth = 6; // Adjust line width as needed
        context.moveTo(wheelCenterX, wheelCenterY);
        context.arc(wheelCenterX, wheelCenterY, wheelRadius - 15, 0, sectionSize);
        context.closePath();

        let gradient = context.createLinearGradient(200, 200, 400, 200);
        gradient.addColorStop(0, "#9d7025");
        gradient.addColorStop(0.5, "#fff98e");
        gradient.addColorStop(0.6, "#fff98e");
        gradient.addColorStop(1, "#9d7025");

        context.strokeStyle = gradient;
        context.stroke();
        context.save();
        context.translate(0, 0);
        context.rotate(Math.PI / 4);
        context.restore();
    })()
</script>
