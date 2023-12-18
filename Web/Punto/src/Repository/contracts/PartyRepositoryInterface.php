<?php

namespace App\Repository\contracts;

use App\Entity\Cell;
use App\Entity\Party;
use App\Entity\Round;
use Doctrine\Common\Collections\Selectable;
use Doctrine\Persistence\ObjectRepository;
use Ramsey\Uuid\UuidInterface;

interface PartyRepositoryInterface extends ObjectRepository, Selectable
{
}
