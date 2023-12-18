<?php

namespace App\Repository\ogm;

use App\Entity\Player;
use App\Repository\contracts\PlayerRepositoryInterface;
use GraphAware\Neo4j\OGM\Repository\BaseRepository;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class PlayerRepository extends BaseRepository implements PlayerRepositoryInterface{
    use OgmCommonMethod;

    public function getPlayer(UuidInterface|string $id): ?Player
    {
        if(is_string($id)){
            $id = Uuid::fromString($id);
        }
        return $this->find((int) $id->getInteger()->toString());
    }
}
