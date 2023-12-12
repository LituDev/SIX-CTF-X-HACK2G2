<?php

namespace App\Rules;

use App\Entity\Party;
use App\Entity\Player;
use App\Entity\PlayerCard;
use App\Entity\Round;

readonly class DeckCard
{
    public function __construct(
        public int $number,
        public int $color
    )
    {
    }

    public function createEntity(Round $round, Player $player) : PlayerCard {
        $entity = new PlayerCard();
        $entity->setColor($this->color);
        $entity->setNumber($this->number);

        $player->addPlayerCard($entity);
        $round->addPlayerCard($entity);

        return $entity;
    }

}
