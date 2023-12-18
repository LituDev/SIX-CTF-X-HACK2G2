<?php

namespace App\Websocket\Authentication;

use App\Repository\contracts\DatabasePool;
use App\Repository\contracts\DatabaseTypes;
use App\Repository\orm\PlayerRepository;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Gos\Bundle\WebSocketBundle\Authentication\Provider\AuthenticationProviderInterface;
use GuzzleHttp\Psr7\Request;
use Ratchet\ConnectionInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class Provider implements AuthenticationProviderInterface
{
    public function __construct(
        private DatabasePool $databasePool,
    )
    {
    }

    public function supports(ConnectionInterface $connection): bool
    {
        $jwt = $this->getJWT($connection);

        if(!isset($jwt->id, $jwt->partyId)){
            return false;
        }

        $jwt = $this->getJWT($connection);
        $database = $jwt->database ?? null;
        if($database === null){
            return false;
        }
        $databaseType = DatabaseTypes::tryFrom($database);
        if($databaseType === null){
            return false;
        }

        $player = $this->databasePool->getPlayerRepository($databaseType)->find($jwt?->id ?? "");
        if($player === null){
            return false;
        }

        return true;
    }

    public function authenticate(ConnectionInterface $connection): TokenInterface
    {
        $jwt = $this->getJWT($connection);
        $databaseType = DatabaseTypes::tryFrom($jwt->database);
        $player = $this->databasePool->getPlayerRepository($databaseType)->find($this->getJWT($connection)?->id ?? "");

        return new Token($player);
    }

    private function getRequest(ConnectionInterface $connection) : Request{
        return $connection->httpRequest;
    }

    private function getCookies(ConnectionInterface $connection) : array{
        $ret = [];
        foreach (explode('; ', $this->getRequest($connection)->getHeader('Cookie')[0] ?? "") as $cookie) {
            $parts = explode('=', $cookie);
            if(count($parts) !== 2){
                continue;
            }
            $ret[trim($parts[0])] = trim($parts[1]);
        }
        return $ret;
    }

    private function getJWT(ConnectionInterface $connection) : ?\stdClass{
        $token = $this->getCookies($connection)['punto_token'] ?? null;
        if($token === null){
            return null;
        }

        try {
            return JWT::decode($token, new Key($_ENV["JWT_SECRET"], "HS256"));
        } catch (\Exception $e){
            return null;
        }
    }
}
