<?php

namespace App\Repository\ogm;

use App\Repository\contracts\CellRepositoryInterface;
use GraphAware\Neo4j\OGM\Repository\BaseRepository;

class CellRepository extends BaseRepository implements CellRepositoryInterface
{
    use OgmCommonMethod;
}
