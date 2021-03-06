const { Builder, By, Key, until } = require('selenium-webdriver');
var fs = require('fs');
var newArray = {
    link: [],
    dom_click: []
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
    // .usingServer(process.env.SELENIUM_REMOTE_URL || 'http://localhost:4444/wd/hub')
        .build();
    // let driver = await new Builder().forBrowser('chrome')
    //     .usingServer(process.env.SELENIUM_REMOTE_URL || 'http://localhost:4444/wd/hub')
    //     .build();
    try {
        await driver.get('' + link + '');
        /**
         * @desc : click trang chủ
         */
        if (typeof newArray.dom_click != 'undefined' && newArray.dom_click.length != 0) {
            // await driver.sleep(1000);
            for (var i = 0; i < newArray.dom_click.length; i++) {
                console.log(data.dom_click[i]);
                await driver.sleep(1000);
                var existed = await driver.findElement(By.css(newArray.dom_click[i])).then(function () {
                    return true; //it existed
                }, function (err) {
                    return false;
                });
                if (existed) {
                    await driver.findElement(By.css(newArray.dom_click[i])).click();
                }
            }
        }
        await driver.sleep(1000);
        await driver.findElement(By.tagName('html')).getAttribute("innerHTML").then(function (profile) {
            // content = profile;
            console.log(profile);
        },function (err) {
            console.log('Not get content');
            process.exit(0);
        });

    }catch(err) {
        process.exit(1);
    }
    finally {
        await driver.quit();
        process.exit(0);
    }
})();
