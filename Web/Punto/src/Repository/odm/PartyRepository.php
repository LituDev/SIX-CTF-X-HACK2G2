<?php

namespace App\Repository\odm;

use App\Entity\Party;
use App\Entity\Round;
use App\Repository\contracts\PartyRepositoryInterface;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Ramsey\Uuid\UuidInterface;

class PartyRepository extends DocumentRepository implements PartyRepositoryInterface
{
}
