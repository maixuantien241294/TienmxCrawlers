var casper = require('casper').create({
    viewportSize: { width: 1280, height: 1000 }
});
var fs = require('fs');
var content = "";

/**
 * get params
 */
var link = casper.cli.get("link");
var web_xpath_active = casper.cli.get("web_xpath_active");
var web_xpath_active_detail = casper.cli.get("web_xpath_active_detail");
var web_xpath_active_cate = casper.cli.get("web_xpath_active_cate");
/**
 * kết thúc get params
 */

casper.start(link, function () {

});
casper.then(function () {
    try {
        var da = new Date();
        var startTime = da.getTime();
        casper.evaluate(function () {
            window.document.body.scrollTop = document.body.scrollHeight;
        });
        /**
         * @desc : click trang chủ
         */
        if (typeof web_xpath_active != 'undefined' && web_xpath_active.length != 0) {
            casper.evaluate(function (btn_click) {
                var element = document.querySelector(btn_click);
                var event = document.createEvent('MouseEvents');
                event.initMouseEvent('click', true, true, window, 1, 0, 0);
                if (element) {
                    element.dispatchEvent(event);
                }
            }, {
                    btn_click: web_xpath_active
                });
        }

        if (typeof web_xpath_active_detail != 'undefined' && web_xpath_active_detail.length != 0) {
            casper.evaluate(function (btn_click) {
                var element = document.querySelector(btn_click);
                var event = document.createEvent('MouseEvents');
                event.initMouseEvent('click', true, true, window, 1, 0, 0);
                if (element) {
                    element.dispatchEvent(event);
                }
            }, {
                    btn_click: web_xpath_active_detail
                });
        }

        if (typeof web_xpath_active_cate != 'undefined' && web_xpath_active_cate.length != 0) {
            casper.evaluate(function (btn_click) {
                var element = document.querySelector(btn_click);
                var event = document.createEvent('MouseEvents');
                event.initMouseEvent('click', true, true, window, 1, 0, 0);
                if (element) {
                    element.dispatchEvent(event);
                }
            }, {
                    btn_click: web_xpath_active_cate
                });
        }

        this.wait(2000, function () {

            var da = new Date();
            var endTime = da.getTime();
            var count_time = endTime - startTime;
            // fs.write('time.html', count_time);
            // this.echo(count_time);
            this.echo(this.getHTML());
            // fs.write("swiss2.html", this.getHTML());
        })
    } catch (e) {
        this.echo(e.toString());
    }
});
casper.run(function () {
    this.exit();
});

