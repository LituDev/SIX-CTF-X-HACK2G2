<?php

namespace App\Entity\Type;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Id\IdGenerator;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class UuidGenerator implements IdGenerator
{
    public function generate(DocumentManager $dm, object $document): UuidInterface
    {
        return Uuid::uuid4();
    }
}
