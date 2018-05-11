var express = require('express');
var app = express();
app.use(express.static("./public"));
app.set("view engine", "ejs");
app.set("views", "./views");

const {
    Builder,
    By,
    Key,
    until
} = require('selenium-webdriver');
var fs = require('fs');
var server = require('http').Server(app);
var io = require('socket.io')(server);
server.listen(3000);
let driver = new Builder().forBrowser('firefox').build();
io.on("connection", function(socket) {

    console.log("co nguoi ket noi:" + socket.id);
    socket.on("Client-send-data", function(data) {
        // console.log(data);
        //   console.log(data.link[0]);
        driver.getCurrentUrl().then(function(link) {
            console.log('open');
            getDataPage(data);
        }).catch(function() {
            console.log('close');
            driver = new Builder().forBrowser('firefox').build();
            getDataPage(data);

        });
    });
});
app.get("/", function(req, res) {
    res.render("trangchu")
});

function getDataPage(data) {
    

    var da = new Date();
    var startTime = da.getTime();
    (async function example() {
        try {
            await driver.get('' + data.link[0] + '');
            if (typeof data.dom_click != 'undefined' && data.dom_click.length != 0) {
                var existed = await driver.findElement(By.css(data.dom_click[0])).then(function() {
                    return true; //it existed
                }, function(err) {
                    return false;
                });
                if (existed) {
                  console.log(data.dom_click[0]);
                    await driver.findElement(By.css(data.dom_click[0])).click();

                }
                await driver.sleep(1000);
            }
            await driver.findElement(By.tagName('html')).getAttribute("innerHTML").then(function(profile) {
                var header = '<base href="https://www.chotot.com/" target="_blank">';
                io.sockets.emit("Sever-send-data", profile);
                console.log('xong');
            });

        } catch(e) {
          console.log('Lỗi: '+ e); 
            // await driver.quit();

        }
    })();
}