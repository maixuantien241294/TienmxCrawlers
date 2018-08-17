const {Builder, By, Key, until} = require('selenium-webdriver');
var firefox = require('selenium-webdriver/firefox');
var fs = require('fs');
var newArray = {
    link: [],
    dom_click: [],
    path_folder: [],
    port:[],
    domain:[],
    web_num_wait:[]
};
process.argv.slice(2).map(function (arg, i) {
    argRule = arg.split('MqFPJ3HnAV');

    if (argRule[0] && argRule[1]) {
        if (typeof newArray[argRule[0]] != "undefined" && newArray[argRule[0]] != null) {
            newArray[argRule[0]].push(argRule[1]);
        } else {
            newArray[argRule[0]] = argRule[1];
        }
    }
});
var da = new Date();
var startTime = da.getTime();
var content = '';
var link = newArray.link
var a = (async function example() {
    let driver = await new Builder().forBrowser('firefox')
        .usingServer(process.env.SELENIUM_REMOTE_URL || 'http://localhost:'+newArray['port'][0]+'/wd/hub')
        .setFirefoxOptions(new firefox.Options().headless())
        .build();
    try {
        await driver.get('' + link + '');
        /**
         * @desc : click trang chủ
         */
        if (typeof newArray.dom_click != 'undefined' && newArray.dom_click.length != 0) {
            // await driver.sleep(1000);
            for (var i = 0; i < newArray.dom_click.length; i++) {
                await driver.sleep(1000);
                var existed = await driver.findElement(By.css(newArray.dom_click[i])).then(function () {
                    return true; //it existed
                }, function (err) {
                    return false;
                });
                console.log(existed);
                if (existed) {
                    console.log(newArray.dom_click[i]);
                    await driver.findElement(By.css(newArray.dom_click[i])).click();
                }
            }
        }
        await driver.executeScript("window.scrollBy(0,2000)")
        await driver.sleep(20);
        await driver.findElement(By.tagName('html')).getAttribute("innerHTML").then(function (profile) {
            var path = newArray['path_folder'][0];
            fs.writeFileSync(path + 'download_file_'+newArray['port'][0]+'.php', profile);
            // console.log(profile);
        }, function (err) {
            console.log('Not get content');
            process.exit(0);
        });
        // await driver.close();
        // await driver.quit();
    } catch (err) {
        await driver.close();
        await driver.quit();
        process.exit(1);
    }
    finally {
        await driver.close();
        await driver.quit();
        process.exit(0);
    }
})();
