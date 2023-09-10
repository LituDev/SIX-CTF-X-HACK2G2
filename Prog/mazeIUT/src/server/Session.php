<?php

namespace lib\server;

use lib\algorithm\Maze;
use React\Socket\ConnectionInterface;

class Session {
	private ?Maze $maze = null;
	private int $win = 0;

	public function __construct(
		private Server $server,
		private ConnectionInterface $connection
	) {
	}

	public function getConnection() : ConnectionInterface {
		return $this->connection;
	}

	public function getMaze() : ?Maze {
		return $this->maze;
	}

	public function setMaze(?Maze $maze) : void {
		$this->maze = $maze;
	}

	public function win() : bool {
		$this->win++;
		if ($this->win >= Server::NUMBER_TIME) {
			$this->connection->end("Bravo, tu as gagné, voilà le flag : " . $this->server->getFlag());

			return true;
		}

		return false;
	}

	public function getWin() : int {
		return $this->win;
	}
}
