<canvas id="wheelCanvas" width="400" height="400"></canvas>

<script>
    (() => {
        // Get the canvas element
        let canvas = document.getElementById('wheelCanvas');
        let context = canvas.getContext('2d');

        // Define the text to display on each section
        let sectionTexts = [@foreach($names as $name) '{{"$name"}}', @endforeach];

        // Define the wheel properties
        let wheelRadius = canvas.width / 2;
        let wheelCenterX = canvas.width / 2;
        let wheelCenterY = canvas.height / 2;
        let numSections = sectionTexts.length;
        let sectionGradients = [
            createGradient('#fa0300', '#fe9614'), // Gradient for section 1
            createGradient('#6711f6', '#0000ea'), // Gradient for section 2
            createGradient('#DAF7A6', '#BBF67A'), // Gradient for section 3
            createGradient('#C70039', '#A3002D'), // Gradient for section 4
            createGradient('#7B241C', '#5C1200'), // Gradient for section 5
            createGradient('#1A5276', '#003D5C'), // Gradient for section 6
            createGradient('#27AE60', '#008E40'), // Gradient for section 7
            createGradient('#F4D03F', '#000000')  // Gradient for section 8
        ];
        let sectionSize = (2 * Math.PI) / numSections;

        context.translate(wheelRadius, wheelRadius);
        context.rotate(-sectionSize / 2 - (Math.PI / 2) );
        context.translate(-wheelRadius, -wheelRadius);

        // Draw the wheel sections
        for (let i = 0; i < numSections; i++) {
            context.beginPath();
            context.moveTo(wheelCenterX, wheelCenterY);
            context.arc(wheelCenterX, wheelCenterY, wheelRadius, i * sectionSize, (i + 1) * sectionSize);
            context.closePath();

            // Fill section with gradient
            context.fillStyle = sectionGradients[i % 2];
            context.fill();

            // Draw text on each section
            context.save();
            context.translate(wheelCenterX, wheelCenterY);
            //middle of the section
            context.rotate(i * sectionSize + sectionSize / 2);
            context.textAlign = 'center';
            context.fillStyle = 'white';
            context.font = 'bold 14px Arial';
            // context.fillText(sectionTexts[i], wheelRadius / 2 + 10, 0, wheelRadius * 0.7);
            drawTextWithLineBreaks(sectionTexts[i], wheelRadius / 2 + 15, 0, wheelRadius * 0.7);
            context.restore();
        }

        // Draw the wheel outline
        context.beginPath();
        context.arc(wheelCenterX, wheelCenterY, wheelRadius - 3, 0, 2 * Math.PI);
        context.lineWidth = 6;
        context.strokeStyle = 'black';
        context.stroke();

        // Function to create gradient
        function createGradient(startColor, endColor) {
            let gradient = context.createRadialGradient(canvas.width / 2, canvas.height / 2, 0, canvas.width / 2, canvas.height / 2, wheelRadius);
            gradient.addColorStop(0, startColor);
            gradient.addColorStop(0.3, endColor);
            gradient.addColorStop(0.9, startColor);
            gradient.addColorStop(1, endColor);
            return gradient;
        }

        function drawTextWithLineBreaks(text, x, y, maxWidth) {
            let words = text.split(' ');
            let line = '';
            let lineHeight = 18; // Adjust as needed
            let yPosition = y;

            for (let i = 0; i < words.length; i++) {
                let testLine = line + words[i] + ' ';
                let metrics = context.measureText(testLine);
                let testWidth = metrics.width;

                if (testWidth > maxWidth && i > 0) {
                    context.fillText(line, x, yPosition);
                    line = words[i] + ' ';
                    yPosition += lineHeight;
                } else {
                    line = testLine;
                }
            }

            context.fillText(line, x, yPosition);
        }
    })()

</script>
