const fs = require('fs');
const puppeteer = require('puppeteer');
const params = require('minimist')(process.argv.slice(2));

async function render() {
    const defaultParams = {
        cookies: [],
        scrollPage: false,
        emulateScreenMedia: true,
        ignoreHttpsErrors: false,
        html: null,
    };

    const browser = await puppeteer.launch({
        // headless : false,
        args: ['--disable-gpu', '--no-sandbox', '--disable-setuid-sandbox']
    });
    const page = await browser.newPage();
    try {
        if (typeof params.link == 'undefined' && params.link.length === 0) {
            console.log('url not found');
        }

        await page.setViewport({ width: 1280, height: 720 });
        await page.goto(params.link, {  timeout: 3000000,waitUntil: 'networkidle2' });

        if (typeof params.web_xpath_active != 'undefined' && params.web_xpath_active.length > 0) {
            // console.log(params.web_xpath_active);return false;
            if (Array.isArray(params.web_xpath_active)) {
                for (let i = 0; i < params.web_xpath_active.length; i++) {
                    var elm = params.web_xpath_active[i];
                    page.click(elm);
                    await page.waitFor(2000);
                }
            }else{
                var elm = params.web_xpath_active;
                page.click(elm);
                await page.waitFor(2000);
            }
        }
        if (typeof params.web_xpath_active_detail != 'undefined' && params.web_xpath_active_detail.length > 0) {

            if (Array.isArray(params.web_xpath_active_detail)) {
                for (let i = 0; i < params.web_xpath_active_detail.length; i++) {
                    var elm = params.web_xpath_active_detail[i];
                    page.click(elm);
                    await page.waitFor(2000);
                }
            }else{
                var elm = params.web_xpath_active_detail;
                page.click(elm);
                await page.waitFor(2000);
            }

        }
        if (typeof params.web_xpath_active_cate != 'undefined' && params.web_xpath_active_cate.length > 0) {
            // console.log(params.web_xpath_active_cate);return false;
            if (Array.isArray(params.web_xpath_active_cate)) {
                for (let i = 0; i < params.web_xpath_active_cate.length; i++) {
                    var elm = params.web_xpath_active_cate[i];
                    page.click(elm);
                    await page.waitFor(2000);
                }
            }else{
                var elm = params.web_xpath_active_cate;
                page.click(elm);
                await page.waitFor(2000);
            }
        }
        const html = await page.content();
        console.log(html);
        // fs.writeFileSync("htmls/test.html", html);
    } catch (err) {
        throw err;
    } finally {
        await browser.close();
    }
}

render();