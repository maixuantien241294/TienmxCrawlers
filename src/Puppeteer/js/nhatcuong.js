const fs = require('fs');
const puppeteer = require('puppeteer');
const params = require('minimist')(process.argv.slice(2));

async function scrapeInfiniteClickItems(page, itemTargetCount = 1000000, scrollDelay = 2000) {
    let items = [];
    try {
        let previousHeight;
        let process = true;
        page.on('console', msg => console.log('PAGE LOG:', msg.text()));
        while (process && (items.length < 60)) {
            const name = params.pag_rule_cate;
            // console.log(name);
            items = await page.evaluate(({ name }) => {

                const extractedElements = document.querySelectorAll(name);
            const items = [];
            for (let element of extractedElements) {
                let data = element.getAttribute('href') + '|' + element.innerText;
                items.push(data);
            }
            return items;

            }, { name });
                if (await page.$(params.pag_dom_click) !== null) {
                    await page.click(params.pag_dom_click)
                    await page.waitFor(scrollDelay);
                } else {
                    process = false;
                }
            }

    } catch (e) {

    }
    return items;
}

(async () => {

    // Set up browser and page.
    const browser = await puppeteer.launch({
    // headless: false,
    args: ['--no-sandbox', '--disable-setuid-sandbox'],
});

const page = await browser.newPage();
await page.goto(params.link);

page.setViewport({ width: 1280, height: 926 });
let items = [];

if (typeof params.item_target_count != 'undefined' && parseInt(params.item_target_count.toString()) > 0) {
    items = await scrapeInfiniteClickItems(page, params.item_target_count);
} else {

    items = await scrapeInfiniteClickItems(page);
}

items.forEach(function (item) {
    console.log(item);
});

await browser.close();
})();