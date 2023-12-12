<?php

namespace App\Websocket\Rpc;

use App\Entity\Board;
use App\Websocket\RpcBase;
use Gos\Bundle\WebSocketBundle\Router\WampRequest;
use Gos\Bundle\WebSocketBundle\RPC\RpcInterface;
use Ratchet\ConnectionInterface;

class BoardRpc implements RpcInterface
{
    use RpcBase;

    public function get(ConnectionInterface $connection, WampRequest $request, $params) : array {
        $jwt = $this->getJWT($connection);
        if(!$this->jwtValid($jwt)){
            return $this->errorResponse('Unauthorized', self::ERROR_CODE_UNAUTHORIZED);
        }
        $databaseType = $this->getDatabaseType($jwt);
        if($databaseType === null){
            return $this->errorResponse('Unauthorized', self::ERROR_CODE_UNAUTHORIZED);
        }
        $party = $this->getParty($jwt["partyId"], $databaseType);
        if(!$party){
            return $this->errorResponse('Party not found', self::ERROR_CODE_NOT_FOUND);
        }
        $round = $party->getCurrentRound();
        if($round === null){
            return $this->errorResponse('Round not found', self::ERROR_CODE_NOT_FOUND);
        }
        $board = new Board($round);

        $ret = [];
        foreach ($board->cells() as $_){
            foreach ($_ as $cell){
                $ret[] = $cell->jsonSerialize();
            }
        }
        return $this->resultResponse([
            "cells" => $ret,
            "nextCard" => $round->isStarted() ? $round->getNextCardFor($round->getNextPlayer()->getPlayer()) : null
        ]);
    }

    public function getName(): string
    {
        return 'board.rpc';
    }
}
