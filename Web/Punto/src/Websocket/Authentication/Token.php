<?php

namespace App\Websocket\Authentication;

use App\Entity\Player;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class Token extends AbstractToken
{
    public function __construct(Player $player)
    {
        parent::__construct($player->getRoles());

        $this->setUser($player);
    }

    public function getCredentials()
    {
        // TODO: Implement getCredentials() method.
    }

    public function __serialize(): array
    {
        // TODO: Implement __serialize() method.
    }

    public function __unserialize(array $data): void
    {
        // TODO: Implement __unserialize() method.
    }
}
