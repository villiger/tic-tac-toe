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

server.get('/game', function(req, res) {
    var gameList = [];
    for (var id in games) {
        game = games[id];
        gameList.push({
            'id'        : game.id,
            'state'     : game.state,
            'winner'    : game.winner,
            'next'      : game.next
        });
    }
    
    res.json(gameList);
});

server.get('/game/:id', function(req, res) {
    game = games[req.params.id];
    if (game) {
        res.json({
            'state'     : game.state,
            'winner'    : game.winner,
            'next'      : game.next
        });
    } else {
        res.send(400); // error 400 Bad Request
    }
});

server.post('/game/create', function(req, res) {
    id = randomHash();
    player0 = randomHash();
    games[id] = {
        'id'        : id,
        'players'   : [player0],
        'state'     : [-1, -1, -1, -1, -1, -1, -1, -1, -1],
        'winner'    : -1,
        'next'      : -1
    };
    
    res.json({
        'id'        : id,
        'player'    : player0
    });
});

server.put('/game/:id/join', function(req, res) {
    game = games[req.params.id];
    if (game) {
        player1 = randomHash();
        game.players.push(player1);
        game.next = Math.floor(Math.random()) % 2; // random select first player (0 or 1)
        res.json({
            'id'        : id,
            'player'    : player0
        });
    } else {
        res.send(400); // error 400 Bad Request
    }
});

server.put('/game/:id/move/:move/player/:player', function(req, res) {
    game = games[req.params.id];
    if (game) {
        // check if the player is in the players array
        playerIndex = game.players.indexOf(req.params.player);
        
        // continue if the index of the player in the player array is the same as the next player
        if (playerIndex == game.next) {
            
            // continue if field is unset
            if (game.state[req.params.move] == -1) {
                game.state[req.params.move] = playerIndex;
                
                // check if player has won the game, field is full (draw) or next turn
                var regex = RegExp('((...){0,2}x{3})|(x..x..x)|(x...x...x)|(..x.x.x..)'.replace('x', playerIndex));
                var stateStr = game.state.join();
                if (regex.exec(stateStr)) {
                    // current player won
                    game.next   = -1;
                    game.winner = playerIndex;
                } else if (game.state.indexOf(-1) == -1) {
                    // no field with -1 in state and nobody has won, game is a draw
                    game.next = -1;
                } else {
                    // nothing of the above, next turn
                    game.next = (game.next + 1) % 2;
                }
                
                res.json({
                    'state'     : game.state,
                    'winner'    : game.winner,
                    'next'      : game.next
                });
                return;
            }
        }
    }
    
    // if we get here respond with error 400 Bad Request
    res.send(400); 
});

server.listen(port);
console.log('Server is listening on port ' + port);
