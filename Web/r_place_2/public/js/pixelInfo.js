let pixelInfo = document.getElementById("pixelInfo");

let oldPixel = { x: -1, y: -1 };

async function updatePixelInfo() {
    if (pixel.x !== oldPixel.x || pixel.y !== oldPixel.y) {
        pixelInfo.classList.toggle("swing-one");
        pixelInfo.onanimationend = () => {
            pixelInfo.classList.toggle("swing-one");
        }

        let response = await fetch(`/api/username/${pixel.x}/${pixel.y}`)

        if (response.ok) {
            let username = await response.text();
            pixelInfo.textContent = `(${pixel.x}, ${pixel.y}) ${username}`;
            oldPixel = { x: pixel.x, y: pixel.y };
        }
    }
}