<?php

namespace lib\server;

use React\Socket\ConnectionInterface;

class SessionManager {
	private array $sessions = [];

	public function addSession(Session $session) : void {
		$this->sessions[] = $session;
	}

	public function removeSession(Session $session) : void {
		$index = array_search($session, $this->sessions);
		if ($index !== false) {
			unset($this->sessions[$index]);
		}
	}

	public function getSessionByConnection(ConnectionInterface $connection) : ?Session {
		foreach ($this->sessions as $session) {
			if ($session->getConnection() === $connection) {
				return $session;
			}
		}

		return null;
	}

	public function removeSessionByConnection(ConnectionInterface $connection) : void {
		$session = $this->getSessionByConnection($connection);
		if ($session !== null) {
			$this->removeSession($session);
		}
	}
}
