<?php

namespace App\Websocket\Topic;

use App\Entity\PartyPlayer;
use App\Websocket\RpcBase;
use Gos\Bundle\WebSocketBundle\Router\WampRequest;
use Gos\Bundle\WebSocketBundle\Topic\TopicInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\Topic;

class PartyTopic implements TopicInterface
{
    use RpcBase;

    public function onSubscribe(ConnectionInterface $connection, Topic $topic, WampRequest $request)
    {
        $jwt = $this->getJwt($connection);
        if(!$this->jwtValid($jwt)) {
            $connection->close();
            return;
        }
        $databaseType = $this->getDatabaseType($jwt);
        if($databaseType === null){
            return;
        }

        $player = $this->getPlayer($jwt["id"], $databaseType);
        $party = $this->getParty($jwt["partyId"], $databaseType);
        $partyPlayer = $party->getPartyPlayer($player);
        if($partyPlayer instanceof PartyPlayer){
            $partyPlayer->setConnected(true);
        }else{
            $party->addPlayer($player);
        }
        $this->databasePool->getObjectManager($databaseType)->persist($party);
        $this->databasePool->getObjectManager($databaseType)->flush();

        $whitelist = $this->getWhitelist($topic, $jwt);

        $topic->broadcast([
            'type' => 'party',
            'action' => 'join',
            'payload' => [
                'player' => $player,
                'id' => $jwt["partyId"],
            ]
        ], eligible: $whitelist);
    }

    public function onUnSubscribe(ConnectionInterface $connection, Topic $topic, WampRequest $request)
    {
        $jwt = $this->getJwt($connection);
        if(!$this->jwtValid($jwt)) {
            $connection->close();
            return;
        }
        $databaseType = $this->getDatabaseType($jwt);
        if($databaseType === null){
            return;
        }

        $player = $this->getPlayer($jwt["id"], $databaseType);
        $party = $this->getParty($jwt["partyId"], $databaseType);
        $partyPlayer = $party->getPartyPlayer($player);
        $partyPlayer->setConnected(false);
        $this->databasePool->getObjectManager($databaseType)->flush();

        $topic->broadcast([
            'type' => 'party',
            'action' => 'disconnect',
            'payload' => [
                'player' => $player,
                'id' => $jwt["partyId"],
            ]
        ], eligible: $this->getWhitelist($topic, $jwt));
    }

    public function onPublish(ConnectionInterface $connection, Topic $topic, WampRequest $request, $event, array $exclude, array $eligible)
    {
        $jwt = $this->getJwt($connection);
        if(!$this->jwtValid($jwt)) {
            $connection->close();
            return;
        }
        $databaseType = $this->getDatabaseType($jwt);
        if($databaseType === null){
            return;
        }

        $topic->broadcast([
            'type' => 'party',
            'action' => 'update',
            'payload' => [
                'player' => $this->getPlayer($jwt["id"], $databaseType),
                'id' => $jwt["partyId"],
                'event' => $event,
            ]
        ], $exclude, eligible: $this->getWhitelist($topic, $jwt));
    }

    public function getName(): string
    {
        return 'party.topic';
    }
}
