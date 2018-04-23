const fs = require('fs');
const puppeteer = require('puppeteer');
const params = require('minimist')(process.argv.slice(2));

(async () => {
  const browser = await puppeteer.launch({
    // headless: false,
    args: ['--no-sandbox', '--disable-setuid-sandbox'],
  });
  const page = await browser.newPage();
  page.setViewport({ width: 1280, height: 926 });
  let items = [];
  await page.goto(params.link);
  if(typeof params.item_target_count != 'undefined' && parseInt(params.item_target_count.toString()) > 0){
    items = await scrapeInfiniteClickItems(page,params.item_target_count);
  }else{
    items = await scrapeInfiniteClickItems(page);
  }
  items.forEach(function (item) {
    console.log(item);
  });
  await browser.close();
})();

async function scrapeInfiniteClickItems(page, itemTargetCount = 1000000, scrollDelay = 2000) {
  let items = [];
  try {
    let viewportHeight = page.viewport().height;
    let previousHeight = await page.evaluate('document.body.scrollHeight');
    let process = true;
    // page.on('console', msg => console.log('PAGE LOG:', msg.text()));
    while ((viewportHeight < previousHeight) && (items.length < itemTargetCount)) {

      viewportHeight = await page.evaluate('document.body.scrollHeight');
      const name = params.pag_rule_cate;

      items = await page.evaluate(({ name }) => {
        const extractedElements = document.querySelectorAll(name);
        const items = [];
        for (let element of extractedElements) {
          let data = element.getAttribute('href') + '|' + element.innerText;
          items.push(data);
        }
        return items;
      }, { name });

      await page.evaluate(_viewportHeight => {
        window.scrollBy(0, _viewportHeight);
      }, previousHeight);

      await page.waitFor(scrollDelay);
      previousHeight = await page.evaluate('document.body.scrollHeight');
    }
  } catch (e) {

  }
  return items;
}