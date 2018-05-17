var io = require('socket.io-client');
var socket = io.connect('http://localhost:3100', {reconnect: true});
var fs = require('fs');
var newArray = {
    link: [],
    dom_click: [],
    path_folder: []
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

socket.on('connect', function () {
    // console.log('Connected');
    send();
    receive(newArray['path_folder'][0]);
})
    .on('connect_error', function () {
        // console.log('Conn error');
        const execFile = require('child_process').execFile;
        const child = execFile('server.bat', [], (error, stdout, stderr) => {
            if (error) {
                throw error;
            }
            console.log(stdout);
        });
    })
    .on('disconnect', function () {
        // console.log('Disconnected');
    });

function receive(path) {
    socket.on('Sever-send-data', function (result) {

        if (result.success === true) {
            console.log(path + 'download_file.php');
            fs.writeFileSync(path + 'download_file.php', result.data);
            process.exit(0);
        } else {
            process.exit(1);
        }

    });
}

function send() {
    socket.emit('Client-send-data', newArray);
    // console.log("send");
}