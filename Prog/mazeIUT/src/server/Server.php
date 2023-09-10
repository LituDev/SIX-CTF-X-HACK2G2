<?php

namespace lib\server;

use lib\algorithm\Maze;
use Monolog\Logger;
use React\Socket\ConnectionInterface;
use React\Socket\SocketServer;
use Shockedplot7560\MonologColored\CustomHandler;

class Server {
	private SocketServer $server;
	private SessionManager $sessionManager;
	private Logger $logger;

	/** @var array<string, int> */
	private static array $connections = [];
	public const NUMBER_TIME = 10;
    public const TIMEOUT = 10;
	public const MAZE_WIDTH = 16;
	public const MAZE_HEIGHT = 16;

	public function __construct(string $host, int $port) {
		$this->logger = new Logger("Server", [
			new CustomHandler(dirname(__DIR__, 2) . "/logs/server.log", Logger::DEBUG)
		]);
		$this->server = new SocketServer($host . ':' . $port);
		$address = $this->server->getAddress();
		$port = parse_url($address, PHP_URL_PORT);
		$this->logger->notice("Server started on port " . $port);
		$this->sessionManager = new SessionManager();

		$this->server->on('connection', function (ConnectionInterface $connection) {
			$this->logger->info("New connection from " . $connection->getRemoteAddress());
			self::$connections[$connection->getRemoteAddress()] = time();
			$session = new Session($this, $connection);
			$this->sessionManager->addSession($session);
			$maze = new Maze(2, 2);
			$maze->generate();
			$session->setMaze($maze);

			$connection->write($maze->__toString());

			$connection->on('data', function ($data) use ($connection) {
				if (time() - self::$connections[$connection->getRemoteAddress()] > self::TIMEOUT) {
					$this->logger->debug("Too long to answer, close connection");
					$connection->end("T'es trop long, tu as perdu\n");

					return;
				}
				$data = trim($data);
				$this->logger->info("Received response from " . $connection->getRemoteAddress() . " : " . $data);
				$session = $this->sessionManager->getSessionByConnection($connection);
				$maze = $session->getMaze();
				$points = explode("|", $data);
				if ($maze->verifyPath($points)) {
					$this->logger->debug("Correct answer, generating new maze");
					if ($session->win()) {
						$this->logger->info("Player " . $connection->getRemoteAddress() . " win");

						return;
					}
					if ($session->getWin() > 2) {
						$maze = new Maze(self::MAZE_WIDTH, self::MAZE_HEIGHT);
						$maze->generate();
					} else {
						switch ($session->getWin()) {
							case 1:
								$string = "# #######
# #   # #
# # # # #
# # #   #
# # #####
# # #   #
# # ### #
#       #
####### #"; #1,7|7,7|7,8
								$maze = Maze::fromArray(Maze::convertStringToGrid($string), [1, 0], [7, 8]);
								break;

							case 2:
								$string = "# #######
#   # # #
# # # # #
# #   # #
# ##### #
#   #   #
# # # ###
# #     #
####### #"; #1,5|3,5|3,7|7,7|7,8
								$maze = Maze::fromArray(Maze::convertStringToGrid($string), [1, 0], [7, 8]);
								break;
							default:
								$maze = new Maze(4, 4);
								$maze->generate();
								break;
						}
					}
					$session->setMaze($maze);
					$connection->write($maze->__toString());
				} else {
					$this->logger->debug("Wrong answer, close connection");
					$connection->end("Pas bon");
				}
			});

			$connection->on('close', function () use ($connection) {
				$this->logger->info("Connection closed from " . $connection->getRemoteAddress());
				$this->sessionManager->removeSessionByConnection($connection);
			});
		});
	}

	public function getFlag() : string {
		return "FLAG{REACT_IS_AWESOME}";
	}
}
