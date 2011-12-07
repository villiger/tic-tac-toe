var crypto = require('crypto');
var journey = require('journey');
var router = new(journey.Router);

var games = {};

router.map(function () {
    this.root.bind(function (req, res) {
        res.send("TicTacToe server");
    });
    
    this.post(/^game\/create$/).bind(function (req, res) {
        id = randomHash();
        key = randomHash();
        games[id] = {
            id      : id,
            players : [key],
            state   : [0, 0, 0, 0, 0, 0, 0, 0, 0],
            winner  : 0,
            next    : 1
        };
        res.send(200, {}, { 'id': id, 'key': key });
    });
    
    this.put(/^game\/([a-z0-9]+)\/join$/).bind(function (req, res, id) {
        game = games[id];
        if (game) {
            key = randomHash();
            game.players.push(key);
            res.send(200, {}, { 'key': key });
        }
    });
});

require('http').createServer(function (request, response) {
    var body = '';
    request.addListener('data', function (chunk) { body += chunk });
    request.addListener('end', function () {
        router.handle(request, body, function (result) {
            response.writeHead(result.status, result.headers);
            response.end(result.body);
        });
    });
}).listen(1337);

function randomHash() {
    value = new Date().getTime();
    shasum = crypto.createHash('sha1');
    shasum.update(value);
    return shasum.digest('hex');
}
