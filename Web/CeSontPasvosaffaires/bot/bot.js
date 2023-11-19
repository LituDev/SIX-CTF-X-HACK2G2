// Thanks to @Mizu for the bot code : https://github.com/ESAIP-CTF/public-esaip-ctf-2023/blob/master/challenges/web/xss_me_luigi/src/bot/bot.js

// required packages
const puppeteer = require("puppeteer");

// variables
// const host = "127.0.0.1:80";

// sleep
const delay = (time) => {
    return new Promise(resolve => setTimeout(resolve, time));
}

// navigate
async function goto(url) {
	const browser = await puppeteer.launch({
		headless: "new",
		ignoreHTTPSErrors: true,
		args: ["--no-sandbox", "--ignore-certificate-errors" ],
		executablePath: "/usr/bin/chromium"
	});


	const page = await browser.newPage();
	await page.setUserAgent('IUT{Th4t_W4s_N0t_Th4t_3asy_huh?_I_Mean_Y0u_C4n_B3_Proud}');

   /* await page.setCookie({
		"name" : "flag",
		"value" : "",
		"domain" : host,
		"httpOnly": false
	});*/
	await page.setDefaultNavigationTimeout(5000);

    // Go to provided URL
	try {
	    await page.goto(url);
	} catch {}

    await delay(1000);

    browser.close();
	return;
}

if (process.argv.length === 2) {
    console.error("No URL provided!");
    process.exit(1);
}

goto(process.argv[2]);