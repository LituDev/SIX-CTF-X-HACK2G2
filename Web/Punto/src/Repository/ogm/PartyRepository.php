<?php

namespace App\Repository\ogm;

use App\Repository\contracts\PartyRepositoryInterface;
use GraphAware\Neo4j\OGM\Repository\BaseRepository;

class PartyRepository extends BaseRepository implements PartyRepositoryInterface
{
    use OgmCommonMethod;
}
