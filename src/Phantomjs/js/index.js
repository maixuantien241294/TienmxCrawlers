var page = require('webpage').create();
var fs = require('fs');
var args = require('system').args;
var newArray = {
    link: [],
    web_xpath_active: [],
    web_xpath_active_detail: [],
    web_xpath_active_cate: []
};
args.forEach(function (arg, i) {
    argRule = arg.split('=');

    if (argRule[0] && argRule[1]) {
        if (typeof newArray[argRule[0]] != "undefined" && newArray[argRule[0]] != null) {
            newArray[argRule[0]].push(argRule[1]);
        } else {
            newArray[argRule[0]] = argRule[1];
        }
    }
});
// console.dir(newArray.web_xpath_active);
page.viewportSize = { width: 1280, height: 800 };
page.scrollPosition = { bottom: 0, left: 0 };
var btn_click = '#show_phone_bnt';
// page.viewportSize = { width: 1200, height: 1024 };
page.open(newArray.link[0], function (status) {
    try {
        // console.log(page.content);

        if (status !== 'success') {
            console.log('Unable to access network');
            phantom.exit();
        } else {
            setTimeout(function () {

                page.evaluate(function () {
                    window.document.body.scrollTop = document.body.scrollHeight;
                });

                if (typeof newArray.web_xpath_active != 'undefined' && newArray.web_xpath_active.length > 0) {
                    const clickActive = page.evaluate(function (s) {
                        var element = document.querySelector(s);
                        var event = document.createEvent('MouseEvents');
                        event.initMouseEvent('click', true, true, window, 1, 0, 0);
                        if (element) {
                            element.dispatchEvent(event);
                        }
                        return s;
                        setTimeout(function () {},1000);
                    }, newArray.web_xpath_active);
                }

                if (typeof newArray.web_xpath_active_detail != 'undefined' && newArray.web_xpath_active_detail.length > 0) {
                    const clickActive = page.evaluate(function (s) {
                        var element = document.querySelector(s);
                        var event = document.createEvent('MouseEvents');
                        event.initMouseEvent('click', true, true, window, 1, 0, 0);
                        if (element) {
                            element.dispatchEvent(event);
                        }
                        return s;
                        setTimeout(function () {},1000);
                    }, newArray.web_xpath_active_detail);
                }

                if (typeof newArray.web_xpath_active_cate != 'undefined' && newArray.web_xpath_active_cate.length > 0) {
                    const clickActive = page.evaluate(function (s) {
                        var element = document.querySelector(s);
                        var event = document.createEvent('MouseEvents');
                        event.initMouseEvent('click', true, true, window, 1, 0, 0);
                        
                        if (element) {
                            element.dispatchEvent(event);
                        }
                        return s;
                        setTimeout(function () {},1000);
                    }, newArray.web_xpath_active_cate);
                }

                console.dir(page.content);
                phantom.exit();
            }, 2000);
        }
    } catch (ex) {
        console.log(ex.toString());
        phantom.exit();
    }
});
