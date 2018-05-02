var casper = require('casper').create({
    viewportSize: { width: 1280, height: 1000 }
});
var fs = require('fs');
var content = "";

/**
 * get params
 */
var link = casper.cli.get("link");
var dom_click = casper.cli.get("dom_click");
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
        if (typeof dom_click != 'undefined' && dom_click.length != 0) {
            casper.evaluate(function (btn_click) {
                var element = document.querySelector(btn_click);
                var event = document.createEvent('MouseEvents');
                event.initMouseEvent('click', true, true, window, 1, 0, 0);
                if (element) {
                    element.dispatchEvent(event);
                }
            }, {
                    btn_click: dom_click
                });
        }

        this.wait(4000, function () {

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

