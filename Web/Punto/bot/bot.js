const fs = require('fs');
const puppeteer = require("puppeteer");
const jwt = require("jsonwebtoken");

require('dotenv').config()

if (!process.env.JWT_SECRET) {
    console.log("JWT_SECRET environment variable not set");
    process.exit(1);
}
const JWT_SECRET = process.env.JWT_SECRET || "o8zPgJ2rb9wyptnRHknkT4TJwUrDPd9npNzHzcGkuwHtu4amv7AmAfyDfQRuzdGoi5raS6UeD8kSq6CdKgkNLLEbJwtzXq9pqJo7BVpwWBSC4kG94mVWWPQZBQZSdM8W";

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

async function accessUrl(url) {
    urlInstance = new URL(url);
    if (urlInstance.hostname !== DOMAIN_NAME) {
        console.log(`Invalid domain name ${urlInstance.hostname}`);
        return;
    }
    const browser = await puppeteer.launch({
        headless: "new",
        ignoreHTTPSErrors: true,
        args: ["--no-sandbox", "--ignore-certificate-errors" ],
        executablePath: "/usr/bin/chromium"
    });

    const page = await browser.newPage();

    console.log(`Accessing http://${DOMAIN_NAME}`);
    await page.goto("http://" + DOMAIN_NAME + "/vestibule");

    console.log("Setting cookie");
    // add cookie to the page
    await page.setCookie({
        name: "punto_token",
        value: token,
        domain: DOMAIN_NAME,
        path: "/",
        httpOnly: false
    });

    console.log(`Accessing ${url}`);
    await page.setDefaultNavigationTimeout(10000);

    try {
        await page.goto(url);
    } catch {}

    console.log("Waiting 1 second");
    await delay(1000);

    console.log("Closing browser");
    await browser.close();
}

async function run() {
    // read all files in the folder /tmp/urls and process them
    const files = fs.readdirSync("/tmp/urls");
    for (const file of files) {
        const url = fs.readFileSync(`/tmp/urls/${file}`, "utf8");
        console.log("Find file " + file);
        console.log(`Processing ${url}`)
        await accessUrl(url);
        console.log(`Processed ${url}`)
        // remove the file
        fs.unlinkSync(`/tmp/urls/${file}`);
    }
}

async function main() {
    while (true) {
        try{
            await run();
        } catch {}
        console.log("Waiting 10 seconds");
        await delay(10000);
    }
}

main();