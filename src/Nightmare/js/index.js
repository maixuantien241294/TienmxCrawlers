var Nightmare = require('nightmare');
var nightmare = Nightmare({ show: false });
var fs = require('fs');
objMod = {};

var newArray = {
    link: [],
    web_xpath_active: [],
    web_xpath_active_detail: [],
    web_xpath_active_cate: []
};
process.argv.slice(2).map(function (arg, i) {
    argRule = arg.split('=');

    if (argRule[0] && argRule[1]) {
        if (typeof newArray[argRule[0]] != "undefined" && newArray[argRule[0]] != null) {
            newArray[argRule[0]].push(argRule[1]);
        } else {
            newArray[argRule[0]] = argRule[1];
        }
    }
});

try {
    const pageNew = nightmare.goto('' + newArray.link + '');
    pageNew.click('._3gN7fJvIS0cWlB_AEtqf1Z')
    pageNew.evaluate(function () {
        var page = document.querySelector('html').innerHTML;
        return page
    }).end().then(function (result) {
        var file = ''
        var result = result;
        file = ' <html lang="vi">';
        file += result;
        file += '</html>'
        fs.writeFileSync('testOutput.html', file);
        // console.log(result)
        if (result != '') {
            //   console.log('xong');
            console.log(file)
        } else {
            console.log('thất bại');
        }
    })
} catch (ex) {
    console.log(ex.toString());
}
