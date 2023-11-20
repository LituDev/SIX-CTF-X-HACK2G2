import puppeteer from "puppeteer";

const solve = async () => {
    const browser = await puppeteer.launch({
        headless: "new",
        args: [
            '--no-sandbox',
            '--use-gl=egl',
            '--disable-dev-shm-usage',
            '--disable-setuid-sandbox'
        ]
    });

    try {
        const page = await browser.newPage();
        await page.goto("http://localhost:3000/");
        await page.waitForSelector("#play-btn");
        await page.click("#play-btn");
        //move mouse to bottom left corner
        await page.mouse.move(0, 0);
        //press space 3 times in a row in a 300ms interval
        await page.keyboard.down("Space");
        await page.waitForTimeout(300);
        await page.keyboard.up("Space");
        await page.keyboard.down("Space");
        await page.waitForTimeout(300);
        await page.keyboard.up("Space");
        await page.keyboard.down("Space");
        await page.waitForTimeout(100000);
    } catch (error) {
        console.error("Error in solve function:", error);
    } finally {
        await browser.close();
    }
}

// Launch 10 bots
const launchBots = async (count) => {
    const promises = [];
    for (let i = 0; i < count; i++) {
        promises.push(solve());
    }

    try {
        await Promise.all(promises);
    } catch (error) {
        console.error("Error in launchBots:", error);
    }
}

launchBots(10);
