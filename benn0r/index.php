<?php

session_start();

class Server {
	
	protected $_games;
	
	protected $_filename;
	
	public function __construct($filename) {
		$this->_filename = $filename;
		
		if (file_exists($filename)) {
			$this->_games = unserialize(file_get_contents($filename));
		} else {
			$this->_games = array();
		}
	}
	
	public function listen($params) {
		switch ($params['action']) {
			case 'join':
				$l = $params['lobby'];
				
				if (isset($this->_games[$l])) {
					foreach ($this->_games[$l]['players'] as $key => $player) {
						if ($player == session_id()) {
							// Spieler schon vorhanden
							switch ($key) {
								case 0:
									return 'symbol X';
								case 1:
									return 'symbol O';
							}
						}
					}
					
					if (count($this->_games[$l]['players']) == 1) {
						// Spieler ins Game hinzufügen
						$this->_games[$l]['players'][] = session_id();
					} else {
						return '0';
					}
					
					if (count($this->_games[$l]['players']) == 2) {
						// Spiel bereit
						return 'symbol O';
					}
				} else {
					// Spiel erzeugen
					$this->_games[$l] = array();
					
					// Array mit Spielern
					$this->_games[$l]['players'] = array();
					$this->_games[$l]['players'][] = session_id();
					
					// 0 = Spieler 1 ist aktiv, 1 = Spieler 2 ist aktiv
					$this->_games[$l]['active'] = 0;
					
					// Hier wird der nächste Zug gespeichert
					$this->_games[$l]['lastturn'] = array(-1, -1);
					
					// Array des Spielfelds
					$this->_games[$l]['field'] = array();
					$this->_games[$l]['field'][0] = array('', '', '');
					$this->_games[$l]['field'][1] = array('', '', '');
					$this->_games[$l]['field'][2] = array('', '', '');
					
					return 'symbol X';
				}
				break;
			case 'tick':
				$l = $this->findLobby(session_id());
				$g = $this->_games[$l];
				
				$x = $params['x'];
				$y = $params['y'];
				
				if ($this->isWinner($g['field'], 'X') || $this->isWinner($g['field'], 'O')) {
					return 'done';
				}
				
				if ($g['players'][$g['active']] == session_id()) {
					if ($g['field'][$x][$y] == '') {
						switch ($g['active']) {
							case 0:
								$g['field'][$x][$y] = 'X';
								$g['active'] = 1; // Jetzt ist wieder Spieler 2
								
								$g['lastturn'][0] = $x;
								$g['lastturn'][1] = $y;
								break;
							case 1:
								$g['field'][$x][$y] = 'O';
								$g['active'] = 0; // Jetzt ist wieder Spieler 1
								
								$g['lastturn'][0] = $x;
								$g['lastturn'][1] = $y;
								break;
						}
						
						echo '1';
					} else {
						echo '0';
					}
				} else {
					echo '0';
				}
				
				$this->_games[$l] = $g; // Game neu speichern
				break;
			case 'wait':
				$l = $this->findLobby(session_id());
				$g = $this->_games[$l];
				
				if ($g['players'][$g['active']] == session_id()) {
					return $g['lastturn'][0] . ' ' . $g['lastturn'][1];
				} else {
					if ($this->isWinner($g['field'], 'X') || $this->isWinner($g['field'], 'O')) {
						return 'done';
					}
					
					return 'wait for it';
				}
				
				break;
		}
	}
	
	public function findLobby($userid) {
		foreach ($this->_games as $lobby => $game) {
			foreach ($game['players'] as $player) {
				if ($player == $userid) {
					return $lobby;
				}
			}
		}
		
		return null;
	}
	
	public function isWinner($f, $s) {
		if (
			// Links nach rechts
			($f[0][0] == $s && $f[1][0] == $s && $f[2][0] == $s) ||
			($f[0][1] == $s && $f[1][1] == $s && $f[2][1] == $s) ||
			($f[0][2] == $s && $f[1][2] == $s && $f[2][2] == $s) ||
			
			// Oben nach unten
			($f[0][0] == $s && $f[0][1] == $s && $f[0][2] == $s) ||
			($f[1][0] == $s && $f[1][1] == $s && $f[1][2] == $s) ||
			($f[2][0] == $s && $f[2][1] == $s && $f[2][2] == $s) ||
			
			// Diagonal
			($f[0][0] == $s && $f[1][1] == $s && $f[2][2] == $s) ||
			($f[0][2] == $s && $f[1][1] == $s && $f[2][0] == $s)
		) {
			// Sieger
			return true;
		}
		
		// Kein Sieger
		return false;
	}
	
	public function __destruct() {
// 		echo '<pre>' . print_r($this->_games, true) . '</pre>';
		
		$f = fopen($this->_filename, 'w+');
		fwrite($f, serialize($this->_games));
		fclose($f);
	}
	
}

$server = new Server('games.txt');
echo $server->listen($_GET);

unset($server); //destruct