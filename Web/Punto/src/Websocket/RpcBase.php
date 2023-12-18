<?php

namespace App\Websocket;

use App\Entity\Party;
use App\Entity\Player;
use App\Repository\contracts\DatabasePool;
use App\Repository\contracts\DatabaseTypes;
use App\Repository\orm\CellRepository;
use App\Repository\orm\PartyRepository;
use App\Repository\orm\PlayerCardRepository;
use App\Repository\orm\PlayerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Gos\Bundle\WebSocketBundle\Authentication\ConnectionRepositoryInterface;
use GuzzleHttp\Psr7\Request;
use Ramsey\Uuid\Uuid;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\Topic;
use React\EventLoop\Loop;

trait RpcBase
{
    public const ERROR_CODE_UNAUTHORIZED = 401;
    public const ERROR_CODE_NOT_FOUND = 404;

    public function __construct(
        private DatabasePool $databasePool,
        private ConnectionRepositoryInterface $connectionRepository
    ) {
        Loop::addTimer(50, function(){
            $this->databasePool->tickDatabases();
        });
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

    private function getJWT(ConnectionInterface $connection) : ?array{
        $token = $this->getCookies($connection)['punto_token'] ?? null;
        if($token === null){
            return null;
        }

        try {
            return (array) JWT::decode($token, new Key($_ENV["JWT_SECRET"], "HS256"));
        } catch (\Exception $e){
            return null;
        }
    }

    private function getTopic(ConnectionInterface $connection): Topic{
        /** @var \SplObjectStorage $spl */
        $spl = $connection->WAMP->subscriptions;
        $spl->rewind();
        return $spl->current();
    }

    /**
     * @return ConnectionInterface[]
     */
    private function getConnections(array $jwt, Topic $topic): array {
        $tokens = [];

        foreach ($this->connectionRepository->findAll($topic) as $tokenConnection){
            $connectionJwt = $this->getJWT($tokenConnection->getConnection());
            if(!$this->jwtValid($connectionJwt)) {
                $tokenConnection->getConnection()->close();
                continue;
            }

            if($connectionJwt["partyId"] === $jwt["partyId"]) {
                $tokens[] = $tokenConnection->getConnection();
            }
        }

        return $tokens;
    }

    /**
     * @return string[]
     */
    private function getWhitelist(Topic $topic, array $jwt): array {
        $tokens = [];
        foreach ($this->connectionRepository->findAll($topic) as $tokenConnection){
            $connectionJwt = $this->getJWT($tokenConnection->getConnection());
            if(!$this->jwtValid($connectionJwt)) {
                $tokenConnection->getConnection()->close();
                continue;
            }

            if($connectionJwt["partyId"] === $jwt["partyId"]) {
                $tokens[] = $tokenConnection->getConnection()->WAMP->sessionId;
            }
        }

        return $tokens;
    }

    private function jwtValid(?array $jwt) : bool {
        return $jwt !== null && isset($jwt["partyId"], $jwt["id"]);
    }

    private function errorResponse(string $message, int $code) : array {
        return [
            'code' => $code,
            'error' => $message
        ];
    }

    private function resultResponse(mixed $result) : array {
        return [
            'result' => $result
        ];
    }

    private function getParty(string $id, DatabaseTypes $databaseTypes) : ?Party{
        $id = Uuid::fromString($id);
        return $this->databasePool->getPartyRepository($databaseTypes)->find($id);
    }

    private function getPlayer(string $id, DatabaseTypes $databaseTypes) : ?Player{
        $id = Uuid::fromString($id);
        return $this->databasePool->getPlayerRepository($databaseTypes)->find($id);
    }

    private function getDatabaseType(ConnectionInterface|array $data) : ?DatabaseTypes {
        if($data instanceof ConnectionInterface){
            $jwt = $this->getJWT($data);
        }else{
            $jwt = $data;
        }
        if(!$this->jwtValid($jwt)){
            return null;
        }
        $database = $jwt["database"] ?? null;
        if($database === null){
            return null;
        }
        return DatabaseTypes::tryFrom($database);
    }
}
