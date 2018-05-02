var casper = require('casper').create();
casper.userAgent('Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:35.0) Gecko/20100101 Firefox/35.0');
casper.options.viewportSize = { width: 1400, height: 600 };
var fs = require('fs');
/**
 * get params
 */
var link = casper.cli.get("link");
var pag_rule_cate = casper.cli.get("pag_rule_cate");
var pag_dom_click = casper.cli.get("pag_dom_click");
var item_target_count = casper.cli.get('item_target_count', 20);
/**
 * kết thúc get params
 */


var links = [];
var process = true;
var count = 1;
casper.start(link, function () {

});
// casper.then(function () {
try {

    while (count < item_target_count) {
        casper.then(function() {
            this.thenClick(pag_dom_click).wait(2000);
            // this.waitForSelector(pag_dom_click, (function() {

            // }), (function() {
            //     // this.die("Timeout reached. Fail whale?");
            //     // this.exit();
            // }), 2000);
            // this.waitForSelector(pag_dom_click, (function() {
            //     this.thenClick(pag_dom_click).wait(2000);
            // }), (function() {
            //     // this.die("Timeout reached. Fail whale?");
            //     // this.exit();
            // }), 2000);
            // this.wait(2000);
        });
        // casper.thenClick(pag_dom_click).wait(2000);
        // casper.evaluate(function (btn_click) {
        //     var element = document.querySelector(btn_click);
        //     var event = document.createEvent('MouseEvents');
        //     event.initMouseEvent('click', true, true, window, 1, 0, 0);
        //     if (element) {
        //         element.dispatchEvent(event);
        //     }
        // }, {btn_click: pag_dom_click});

        // this.waitForSelector(pag_dom_click,
        //     function pass() {
        //         console.log('find');
        //     },
        //     function fail() {
        //         console.log('find');
        //     },
        //     20000 // timeout limit in milliseconds
        // );
        casper.then(function () {
            links = this.evaluate(function (page_rule) {
                var links = document.querySelectorAll(page_rule);
                return Array.prototype.map.call(links, function (element) {
                    return element.getAttribute('href') + '|' + element.innerText;
                    ;
                });
            }, { page_rule: pag_rule_cate });
            // console.log(links.join('\n'))

        });
        count++;
        // this.wait(2000, function () {
        //     count++;
        // });
    }
    // this.wait(20000, function () {
    //     links = this.evaluate(function (page_rule) {
    //         var links = document.querySelectorAll(page_rule);
    //         return Array.prototype.map.call(links, function (element) {
    //             return element.getAttribute('href') + '|' + element.innerText;
    //             ;
    //         });

    //     }, { page_rule: pag_rule_cate });
    //     fs.write("swiss2.html", this.getHTML());
    // })
} catch (e) {
    this.echo(e.toString());
}
// });

casper.run(function () {
    // this.echo(links.length + ' links found:');
    this.echo(links.join('\n')).exit();
    // fs.write("swiss2.html", this.getHTML());
    this.exit();
});