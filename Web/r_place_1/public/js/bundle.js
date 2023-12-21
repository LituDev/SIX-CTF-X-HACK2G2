// public/js/toolbar.js

let colorPicker = document.getElementById("colorContainer");
let cooldownButton = document.getElementById("cooldownButton");

let oldSelectedColorBlock = null;

function deselectColor() {
    if(oldSelectedColorBlock !== null) {
        oldSelectedColorBlock.className = 'color-block';
    }
    selectedColor = -1;
    cursor.style.backgroundColor = "transparent";
    selectedPixel.style.backgroundColor = "transparent";
}

async function initPalette() {
    try {
        let response = await fetch("/misc/colors.json",
            {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                }
            });
        if (response.ok) {
            colors = await response.json();
            colors = colors.colors;
        } else {
            console.error(await response.text());
        }
    } catch (error) {
        console.error("Error:", error);
    }

    colors.forEach((color) => {
        const colorBlock = document.createElement('button');
        colorBlock.className = 'color-block';
        colorBlock.style.backgroundColor = color;
        colorBlock.setAttribute('aria-label', `Select color ${color}`);
        colorBlock.addEventListener('click', () => {
            deselectColor();
            selectedColor = colors.indexOf(color);
            colorBlock.className = 'color-block selected-color-block';
            oldSelectedColorBlock = colorBlock;
            selectedPixel.style.backgroundColor = color;
            showDrawButton();
        });
        colorPicker.appendChild(colorBlock);
    });
}

function switchState(state) {
    cooldownButton.style.display = "none";
    colorPicker.style.display = "none";
    drawButton.style.display = "none";

    if(state === "cooldown") {
        cooldownButton.style.display = "block";
    } else if(state === "palette") {
        colorPicker.style.display = "grid";
        showDrawButton();
    }
}

function showDrawButton() {
    if(oldPixel && oldPixel.x !== -1 && oldPixel.y !== -1 && selectedColor !== -1 && drawButton.style.display === "none") {
        drawButton.style.display = "block";
        drawButton.classList.toggle("swing-one");
        drawButton.onanimationend = () => {
            drawButton.classList.toggle("swing-one");
        }
    } else if((oldPixel && oldPixel.x === -1 && oldPixel.y === -1) || selectedColor === -1) {
        drawButton.style.display = "none";
    }
}

function updateCooldownDisplay() {
    if (localCooldown <= 0) {
        switchState("palette");
        return;
    } else {
        switchState("cooldown");
    }
    cooldownButton.textContent = `${Math.ceil(localCooldown)} seconds`;
    localCooldown--;
    setTimeout(updateCooldownDisplay, 1000);
}

// public/js/app.js

let cursor = document.getElementById("cursor");
let canvas = document.getElementById("canvas");
let selectedPixel = document.getElementById("selectedPixel");
let drawButton = document.getElementById("drawButton");

let ctx = canvas.getContext('2d');
ctx.imageSmoothingEnabled = false;
ctx.mozImageSmoothingEnabled = false;
ctx.webkitImageSmoothingEnabled = false;
ctx.msImageSmoothingEnabled = false;

const maxZoom = 128;
const minZoom = 0.75;

let currentZoom = 1;
let isDragging = false;
let recentlyDragged = false;
let lastPosition = { x: 0, y: 0 };
let offset = { x: 0, y: 0 };
let pixel = { x: 0, y: 0 };
let colors;
let selectedColor = -1;
let socket;
let localCooldown = 0;

async function initApp() {
    await initPalette();
    await getGrid();
    initSocket();
    cursorPosition({clientX: 0, clientY: 0});
    selectedPixelPosition();
    switchState("palette");
}

function initSocket() {
    let wsProtocol = window.location.protocol === 'https:' ? 'wss' : 'ws';
    socket = new WebSocket(`${wsProtocol}://${window.location.host}/api/ws`);

    socket.onmessage = function(event) {
        let data = JSON.parse(event.data);
        ctx.fillStyle = colors[data.color];
        ctx.fillRect(data.x, data.y, 1, 1);
    }

    socket.onerror = function(error) {
        console.log(`[error] ${error.message}`);
    }
}

async function sendPixel() {
    if (selectedColor === -1) return;

    try {
        const response = await fetch('/api/draw', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                x: oldPixel.x,
                y: oldPixel.y,
                color: selectedColor
            })
        });

        if (response.ok) {
            localCooldown = await response.json();
            deselectColor();
            updateCooldownDisplay();
        } else {
            console.error(await response.text());
        }
    } catch (error) {
        console.error("Error:", error);
    }
}

async function getGrid() {
    try {
        const sizeResponse = await fetch('/api/size');
        const sizeData = await sizeResponse.json();
        canvas.width = sizeData[0];
        canvas.height = sizeData[1];

        const pngResponse = await fetch('/api/png', {
            method: 'GET',
            headers: {
                'Accept': 'image/png'
            }
        });
        const blob = await pngResponse.blob();
        const imageUrl = URL.createObjectURL(blob);

        const img = new Image();
        img.src = imageUrl;
        img.onload = () => {
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0);
            URL.revokeObjectURL(img.src);
        };

        const updatesResponse = await fetch('/api/updates');
        const updates = await updatesResponse.json();
        const ctx = canvas.getContext('2d');
        updates.forEach(update => {
            ctx.fillStyle = colors[update.color];
            ctx.fillRect(update.x, update.y, 1, 1);
        });
    } catch (error) {
        console.error('Error loading grid:', error);
    }
}

function cursorPosition(event){
    const canvasBounds = canvas.getBoundingClientRect();

    let x = Math.floor((event.clientX - canvasBounds.left) / currentZoom);
    let y = Math.floor((event.clientY - canvasBounds.top) / currentZoom);
    pixel = { x, y };

    cursor.style.left = `${canvasBounds.left + x * currentZoom - currentZoom * 0.1}px`;
    cursor.style.top = `${canvasBounds.top + y * currentZoom - currentZoom * 0.1}px`;
    cursor.style.width = `${currentZoom * 1.2}px`;
    cursor.style.height = `${currentZoom * 1.2}px`;
}

function selectedPixelPosition(){
    const canvasBounds = canvas.getBoundingClientRect();

    selectedPixel.style.left = `${canvasBounds.left + (oldPixel.x + 1) * currentZoom - currentZoom}px`;
    selectedPixel.style.top = `${canvasBounds.top + (oldPixel.y + 1) * currentZoom - currentZoom}px`;
    selectedPixel.style.width = `${currentZoom * 1.01}px`;
    selectedPixel.style.height = `${currentZoom * 1.01}px`;
}

canvas.addEventListener('wheel', (event) => {
    let prevZoom = currentZoom;
    currentZoom = event.wheelDelta > 0 ? currentZoom * 1.1 : currentZoom / 1.1;
    currentZoom = Math.min(Math.max(currentZoom, minZoom), maxZoom);

    offset.x -= (canvas.width / 2 - offset.x) * (currentZoom - prevZoom) / prevZoom;
    offset.y -= (canvas.height / 2 - offset.y) * (currentZoom - prevZoom) / prevZoom;

    canvas.style.transform = `translate(${offset.x}px, ${offset.y}px) scale(${currentZoom})`;

    cursorPosition(event);
    selectedPixelPosition();
}, { passive: true });

canvas.addEventListener('mousedown', (event) => {
    isDragging = true;
    lastPosition.x = event.clientX;
    lastPosition.y = event.clientY;
});

canvas.addEventListener('mouseup', () => {
    if (isDragging) {
        setTimeout(() => {
            recentlyDragged = false;
        }, 50);
    }
    isDragging = false;
});

canvas.addEventListener('mousemove', (event) => {
    if (isDragging) {
        recentlyDragged = true;

        const dx = event.clientX - lastPosition.x;
        const dy = event.clientY - lastPosition.y;

        offset.x += dx;
        offset.y += dy;

        canvas.style.transform = `translate(${offset.x}px, ${offset.y}px) scale(${currentZoom})`;

        lastPosition.x = event.clientX;
        lastPosition.y = event.clientY;
    }

    cursorPosition(event);
    selectedPixelPosition();
});

canvas.addEventListener('mouseleave', () => {
    isDragging = false;
    cursor.classList.add("hidden");
});

canvas.addEventListener('mouseenter', () => {
    cursor.classList.remove("hidden");
});

canvas.addEventListener('click', async () => {
    if (!recentlyDragged) {
        await updatePixelInfo();
        selectedPixelPosition();
        showDrawButton();
    }
});

drawButton.addEventListener('click', sendPixel);

initApp();

// public/js/pixelInfo.js

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

