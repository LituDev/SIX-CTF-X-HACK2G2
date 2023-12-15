const fs = require('fs');
const puppeteer = require("puppeteer");
const jwt = require("jsonwebtoken");

require('dotenv').config()

if (!process.env.JWT_SECRET) {
    console.log("JWT_SECRET environment variable not set");
    process.exit(1);
}
const JWT_SECRET = process.env.JWT_SECRET || "secret";

const DOMAIN_NAME = process.env.DOMAIN_NAME || "nginx";

const token = jwt.sign({
    id: "00000000-0000-0000-0000-000000000001",
    partyId: null,
    database: "mysql",
    granted: true
}, JWT_SECRET);

// create /tmp/urls if it doesn't exist
if (!fs.existsSync("/tmp/urls")) {
    fs.mkdirSync("/tmp/urls");
}

const delay = (time) => {
    return new Promise(resolve => setTimeout(resolve, time));
}

async function accessUrl(code) {
    const browser = await puppeteer.launch({
        headless: "new",
        ignoreHTTPSErrors: true,
        args: ["--no-sandbox", "--ignore-certificate-errors" ],
        executablePath: "/snap/bin/chromium"
    });

    const page = await browser.newPage();
    await page.goto("http://" + DOMAIN_NAME + "/vestibule?punto_token=" + token);

    await page.type("body #join_party_form_code", code);
    await page.screenshot({path: '/tmp/screenshot5.png'});
    await page.keyboard.press("Enter");
    await page.screenshot({path: '/tmp/screenshot3.png'});

    try {
        await page.waitForFunction("window.location.pathname != '/vestibule'")
    } catch {
        return;
    }

    await delay(3000);
    await browser.close();
}

async function run() {
    var promises = [];
    // read all files in the folder /tmp/urls and process them
    const files = fs.readdirSync("/tmp/urls");
    for (const file of files) {
        try {
            const code = fs.readFileSync(`/tmp/urls/${file}`, "utf8");
            console.log("Find file " + file);
            console.log(`Processing party ${code}`)
            let promise = accessUrl(code);
            promises.push(promise);
            await promise;
            console.log(`Processed party ${code}`)
        } catch (e){
            console.error(e);
        } finally {
            fs.unlinkSync(`/tmp/urls/${file}`);
        }
    }

    return Promise.all(promises);
}

async function main() {
    while (true) {
        await run();
        console.log("Waiting 10 seconds");
        await delay(10000);
    }
}

main();