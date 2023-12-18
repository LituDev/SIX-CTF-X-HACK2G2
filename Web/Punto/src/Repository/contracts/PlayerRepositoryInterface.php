<?php

namespace App\Repository\contracts;

use App\Entity\Player;
use Doctrine\Common\Collections\Selectable;
use Doctrine\Persistence\ObjectRepository;
use Ramsey\Uuid\UuidInterface;

interface PlayerRepositoryInterface extends ObjectRepository, Selectable
{
    public function getPlayer(string|UuidInterface $id): ?Player;
}
