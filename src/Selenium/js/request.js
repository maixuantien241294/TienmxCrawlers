var io = require('socket.io-client');
var socket = io.connect('http://localhost:3100', { reconnect: true });
var newArray = {
    link: [],
    dom_click: []
};
process.argv.slice(2).map(function(arg, i) {
    argRule = arg.split('MqFPJ3HnAV');

    if (argRule[0] && argRule[1]) {
        if (typeof newArray[argRule[0]] != "undefined" && newArray[argRule[0]] != null) {
            newArray[argRule[0]].push(argRule[1]);
        } else {
            newArray[argRule[0]] = argRule[1];
        }
    }
});


socket.on('connect', function() {
    // console.log('Connected');
    send();
    receive();
})
    .on('connect_error', function() {
        // console.log('Conn error');
        const execFile = require('child_process').execFile;
        const child = execFile('server.bat', [], (error, stdout, stderr) => {
            if (error) {
                throw error;
            }
            console.log(stdout);
        });
    })
    .on('disconnect', function() {
        // console.log('Disconnected');
    });

function receive() {
    socket.on('Sever-send-data', function(result) {

        if (result.success === true) {
            console.log(result.data);
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