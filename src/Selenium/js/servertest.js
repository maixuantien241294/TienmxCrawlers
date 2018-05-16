var express = require('express');
var app = express();
app.use(express.static("./public"));
app.set("view engine", "ejs");
app.set("views", "./views");

const {Builder, By, Key, until} = require('selenium-webdriver');
var fs = require('fs');
var server = require('http').Server(app);
var io = require('socket.io')(server);
server.listen(3000);
let driver = new Builder().forBrowser('firefox').build();
io.on("connection", function (socket) {

    console.info("Co ket noi:" + socket.id);
    socket.on("Client-send-data", function (data) {
        console.log('Start');
        driver.getCurrentUrl().then(function (link) {
            console.log('Trinh duyet dang mo');
            getDataPage(data);
            console.log('');
        }).catch(function () {
            console.log('Trinh duyet bi dong');
            driver = new Builder().forBrowser('firefox').build();
            console.log('Mo lai trinh duyet');
            getDataPage(data);
            console.log('');
        });
    });
});
app.get("/", function (req, res) {
    res.render("trangchu")
});

function getDataPage(data) {


    var da = new Date();
    (async function example() {
        try {
            console.info("Crawler link: "+data.link[0]);
            await driver.get('' + data.link[0] + '');
            if (typeof data.dom_click != 'undefined' && data.dom_click.length != 0) {
                for (var i=0;i<data.dom_click.length;i++){
                    await driver.sleep(1000);
                    var existed = await
                        driver.findElement(By.css(data.dom_click[i])).then(function () {
                            return true; //it existed
                        }, function (err) {
                            return false;
                        });
                    console.log(existed);
                    if (existed) {
                        await driver.findElement(By.css(data.dom_click[i])).click();
                    }
                }

            }
            await driver.sleep(1000);
            await driver.findElement(By.tagName('html')).getAttribute("innerHTML").then(function (profile) {
                var data = {
                    data:profile,
                    success:true
                }
                io.sockets.emit("Sever-send-data", data);
                console.log('Complete');
                console.log('');
            }, function (err) {
                var data = {
                    data: '',
                    success: false
                }
                io.sockets.emit("Sever-send-data", data);
                console.error('Timeout: '+err);
            });

        } catch (e) {
            console.error('Lỗi: ' + e);
            await driver.quit();
        }
    })();
}