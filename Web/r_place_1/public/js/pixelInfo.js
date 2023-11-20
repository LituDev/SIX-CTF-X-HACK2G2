let pixelInfo = document.getElementById("pixelInfo");

let oldPixel = { x: -1, y: -1 };

async function updatePixelInfo() {
    if (pixel.x !== oldPixel.x || pixel.y !== oldPixel.y) {
        pixelInfo.classList.toggle("swing-one");
        pixelInfo.onanimationend = () => {
            pixelInfo.classList.toggle("swing-one");
        }

        pixelInfo.textContent = `(${pixel.x}, ${pixel.y})`;
        oldPixel = { x: pixel.x, y: pixel.y };
    }
}