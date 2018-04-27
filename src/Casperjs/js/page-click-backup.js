var casper = require('casper').create();
var fs = require('fs');
/**
 * get params
 */
var link = casper.cli.get("link");
var pag_rule_cate = casper.cli.get("pag_rule_cate");
var pag_dom_click = casper.cli.get("pag_dom_click");
var item_target_count = casper.cli.get('item_target_count',20);
/**
 * kết thúc get params
 */


var links = [];
var process = true;
var count = 1;
casper.start(link, function () {

});
casper.then(function () {
    try {

        while (count < item_target_count) {
            var testClick = casper.evaluate(function (btn_click) {
                var element = document.querySelector(btn_click);
                var event = document.createEvent('MouseEvents');
                var process = true;
                event.initMouseEvent('click', true, true, window, 1, 0, 0);
                if (element) {
                    element.dispatchEvent(event);
                } else {
                    process = false;
                }
                return process;

            }, {
                btn_click: pag_dom_click
            });
            count++;
        }
        this.wait(2000, function () {
            links = this.evaluate(function (page_rule) {
                var links = document.querySelectorAll(page_rule);
                return Array.prototype.map.call(links, function (element) {
                    return element.getAttribute('href') + '|' + element.innerText;
                    ;
                });

            }, {page_rule: pag_rule_cate});
        })
    } catch (e) {
        this.echo(e.toString());
    }
});

casper.run(function () {
    this.echo(links.join('\n')).exit();
    this.exit();
});