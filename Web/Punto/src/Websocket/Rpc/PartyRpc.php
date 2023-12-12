<?php

namespace App\Websocket\Rpc;

use App\Entity\Board;
use App\Entity\Cell;
use App\Entity\PartyPlayer;
use App\Repository\contracts\DatabaseTypes;
use App\Rules\Deck;
use App\Rules\DeckPosition;
use App\Rules\Punto;
use App\Websocket\RpcBase;
use Gos\Bundle\WebSocketBundle\Router\WampRequest;
use Gos\Bundle\WebSocketBundle\RPC\RpcInterface;
use Ratchet\ConnectionInterface;

class PartyRpc implements RpcInterface
{
    use RpcBase;

    public function connected(ConnectionInterface $connection, WampRequest $request, $params) : array{
        $ret = [];
        $jwt = $this->getJWT($connection);
        $databaseType = $this->getDatabaseType($jwt);
        if($databaseType === null){
            return $this->errorResponse('Unauthorized', self::ERROR_CODE_UNAUTHORIZED);
        }
        $party = $this->getParty($jwt["partyId"], $databaseType);
        if($party === null){
            return $this->errorResponse("Invalid party", 0);
        }
        foreach ($this->getConnections($jwt, $this->getTopic($connection)) as $tokenConnection){
            $conJwt = $this->getJWT($tokenConnection);
            $player = $this->getPlayer($conJwt["id"], $databaseType);
            $ret[] = [
                "player" => $player,
                "connected" => $party->getPartyPlayer($player)->isConnected(),
                "position" => $party->getPartyPlayer($player)->getPosition(),
            ];
        }

        return [
            "next" => $party->getCurrentRound()?->getNextPlayer(),
            "result" => $ret
        ];
    }

    public function start(ConnectionInterface $connection, WampRequest $request, $params) : array {
        $jwt = $this->getJWT($connection);
        if(!$this->jwtValid($jwt)) {
            return $this->errorResponse("Invalid JWT", 0);
        }
        $databaseType = $this->getDatabaseType($jwt);
        if($databaseType === null){
            return $this->errorResponse('Unauthorized', self::ERROR_CODE_UNAUTHORIZED);
        }

        $topic = $this->getTopic($connection);
        $whitelist = $this->getWhitelist($topic, $jwt);


        $party = $this->getParty($jwt["partyId"], $databaseType);
        if($party === null){
            return $this->errorResponse("Invalid party", 0);
        }
        if($databaseType !== DatabaseTypes::NEO4J){
            $this->databasePool->getObjectManager($databaseType)->refresh($party);
        }

        if(count($party->getFinishedRounds()) >= $party->getRoundNumber()){
            return $this->errorResponse("Party already finished", 0);
        }

        $round = $party->getCurrentRound();
        if($round === null){
            $round = $party->createRound();
            $this->databasePool->getObjectManager($databaseType)->persist($round);
            $this->databasePool->getObjectManager($databaseType)->flush();
        }

        if($round->isStarted()){
            return $this->errorResponse("Round already started", 0);
        }

        $players = $party->getPartyPlayers();
        if(count($players) < 2){
            return $this->errorResponse("Not enough players", 0);
        }

        $deck = new Deck(count($players));
        $commonColor = null;
        foreach (array_values($players->toArray()) as $key => $player) {
            $cards = $deck->distributeForPlayer($key, $commonColor);
            for ($i = 0; $i < count($cards); $i++) {
                $entity = $cards[$i]->createEntity($round, $player->getPlayer());
                $entity->setPosition($i);
                $this->databasePool->getObjectManager($databaseType)->persist($entity);
            }
        }
        $round->setCommonColor($commonColor);
        $round->setStartedAt(new \DateTimeImmutable());
        $ps = $party->getPartyPlayers()->toArray();
        shuffle($ps);
        foreach ($ps as $key => $partyPlayer){
            $partyPlayer->setPosition($key);
        }
        $this->databasePool->getObjectManager($databaseType)->flush();

        $next = $round->getNextPlayer();
        $topic->broadcast([
            'type' => 'party',
            'action' => 'start',
            'payload' => [
                'party' => $party,
                "next" => $next,
                "nextCard" => $round->getNextCardFor($next->getPlayer()),
            ]
        ], eligible: $whitelist);

        return [];
    }

    public function addCard(ConnectionInterface $connection, WampRequest $request, $params) : array{
        $jwt = $this->getJWT($connection);
        if(!$this->jwtValid($jwt)) {
            return $this->errorResponse("Invalid JWT", 0);
        }
        $databaseType = $this->getDatabaseType($jwt);
        if($databaseType === null){
            return $this->errorResponse('Unauthorized', self::ERROR_CODE_UNAUTHORIZED);
        }
        $topic = $this->getTopic($connection);
        $whitelist = $this->getWhitelist($topic, $jwt);

        $party = $this->getParty($jwt["partyId"], $databaseType);
        if($party === null){
            return $this->errorResponse("Invalid party", 0);
        }
        $round = $party->getCurrentRound();
        if($round === null){
            return $this->errorResponse("Round not started", 0);
        }
        if(!$round->isStarted()){
            return $this->errorResponse("Round not started", 0);
        }
        if($round->isFinished()){
            return $this->errorResponse("Round already finished", 0);
        }
        $player = $this->getPlayer($jwt["id"], $databaseType);
        if($round->getNextPlayer()->getPlayer()->getId()->toString() !== $player->getId()->toString()){
            return $this->errorResponse("Not your turn", 0);
        }

        $board = new Board($round);
        $cell = $board->getCell($params['x'], $params['z']);
        $deckPosition = new DeckPosition($board);
        if(!$deckPosition->canAddAt($params['x'], $params['z'])){
            return $this->errorResponse("Invalid position", 0);
        }

        if($cell === null){
            $cell = new Cell();
            $cell->setX($params['x']);
            $cell->setZ($params['z']);
            $round->addCell($cell);
            $this->databasePool->getObjectManager($databaseType)->persist($cell);
            $this->databasePool->getObjectManager($databaseType)->flush();
        }

        $card = $round->getNextCardFor($player);
        foreach ($cell->getPlayerCards() as $actualCard){
            if($actualCard->getNumber() >= $card->getNumber()){
                return $this->errorResponse("The number is too low", 0);
            }
        }
        $cell->addPlayerCard($card);
        $round->setLastPlayedPlayer($player);
        $this->databasePool->getObjectManager($databaseType)->flush();

        $this->databasePool->getObjectManager($databaseType)->refresh($party);
        $this->databasePool->getObjectManager($databaseType)->refresh($round);
        $punto = new Punto(new Board($round));
        if(($winner = $punto->getWinner(count($party->getPartyPlayers()))) !== null){
            $winnerPlayer = $party->getPartyPlayer($winner);
            $round->setFinishedAt(new \DateTimeImmutable());
            $winnerPlayer->addWinnedRound($round);

            $this->databasePool->getObjectManager($databaseType)->persist($round);

            if(count($party->getFinishedRounds()) >= $party->getRoundNumber()){
                $party->setFinishedAt(new \DateTimeImmutable());

                $party->setWinner($party->calculateWinner());

                $topic->broadcast([
                    'type' => 'party',
                    'action' => 'end',
                    'payload' => [
                        'party' => $party,
                        'winner' => $party->getWinner()->getPlayer()->getName(),
                    ]
                ], eligible: $whitelist);
            }else {
                $topic->broadcast([
                    'type' => 'party',
                    'action' => 'win',
                    'payload' => [
                        'party' => $party,
                        'winner' => $winner,
                    ]
                ], eligible: $whitelist);
            }

            $this->databasePool->getObjectManager($databaseType)->flush();
        }
        $this->databasePool->getObjectManager($databaseType)->refresh($party);

        $next = $round->getNextPlayer();
        if(!$party->isFinished()){
            $topic->broadcast([
                'type' => 'party',
                'action' => 'addCard',
                'payload' => [
                    "next" => $next,
                    "nextCard" => $round->getNextCardFor($next->getPlayer()),
                    'player' => $player,
                    'cell' => $cell,
                ]
            ], eligible: $whitelist);
        }

        return $this->resultResponse([
            "success" => true
        ]);
    }

    public function getName(): string
    {
        return 'party.rpc';
    }
}
