<?php

namespace App\Repository\odm;

use App\Entity\Player;
use App\Repository\contracts\PlayerRepositoryInterface;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class PlayerRepository extends DocumentRepository implements PlayerRepositoryInterface
{
    public function getPlayer(UuidInterface|string $id): ?Player
    {
        if(is_string($id)){
            $id = Uuid::fromString($id);
        }

        return $this->find($id);
    }
}
