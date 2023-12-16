let colorPicker = document.getElementById("colorContainer");
let cooldownButton = document.getElementById("cooldownButton");
let notConnected = document.getElementById("notConnected");
let notVerified = document.getElementById("notVerified");

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
    notConnected.style.display = "none";
    notVerified.style.display = "none";

    if(state === "cooldown") {
        cooldownButton.style.display = "block";
    } else if(state === "palette") {
        colorPicker.style.display = "grid";
        showDrawButton();
    } else if(state === "notConnected") {
        notConnected.style.display = "block";
    } else if(state === "notVerified") {
        notVerified.style.display = "block";
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

document.addEventListener('DOMContentLoaded', () => {
    notConnected.addEventListener('click', toggleMenu);
});