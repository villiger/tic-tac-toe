var crypto = require('crypto');
var express = require('express');

var port = 1337;
var server = express.createServer();
var games = {};

function randomHash() {
    value = new Date().getTime().toString() + Math.random().toString();
    shasum = crypto.createHash('sha1');
    shasum.update(value);
    return shasum.digest('hex');
}

server.use(express.static(__dirname + '/public'));

server.post('/game/create', function(req, res) {
    id = randomHash();
    playerKey = randomHash();
    games[id] = {
        id      : id,
        players : [playerKey],
        state   : [0, 0, 0, 0, 0, 0, 0, 0, 0],
        winner  : 0,
        next    : 1
    };
    res.json({ 'id': id, 'key': playerKey });
});

server.put('/game/:id/join', function(req, res) {
    game = games[req.param.id];
    if (game) {
        playerKey = randomHash();
        game.players.push(playerKey);
        res.json({ 'key': playerKey, 'success': true });
    } else {
        res.json({ 'success': false });
    }
});

server.listen(port);
console.log('Server is listening on port ' + port);
