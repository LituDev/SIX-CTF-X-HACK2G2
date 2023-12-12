<?php

namespace App\Repository\odm;

use App\Entity\Player;
use App\Repository\contracts\CellRepositoryInterface;
use App\Repository\contracts\PlayerRepositoryInterface;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class CellRepository extends DocumentRepository implements CellRepositoryInterface
{
}
