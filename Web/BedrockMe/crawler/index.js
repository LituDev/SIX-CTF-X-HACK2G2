
const puppeteer = require('puppeteer');

const login_url='http://localweb';

async function run()
{
    console.log("Start crawling");
    const browser = await puppeteer.launch({
        headless: "new",
        ignoreHTTPSErrors: true,
        args: ["--no-sandbox", "--ignore-certificate-errors" ],
        executablePath: "/usr/bin/chromium"
    });

    const page = await browser.newPage();

    var  cookies = [
        {
            "name": "FLAG",
            "value": process.env.FLAG,
            "domain": "localweb",
            "httpOnly": false
        }
    ]

    await page.setCookie(...cookies);
    await page.goto(login_url, {waitUntil: 'load'});

    // Type our username and password
    await page.type('input#server', "localserver:19132");

    // Submit form
    await page.click('#submit');
    await page.waitForTimeout(1000);

    browser.close();
}

console.log("Start crawler");

// run all 30 seconds
setInterval(run, 30000);