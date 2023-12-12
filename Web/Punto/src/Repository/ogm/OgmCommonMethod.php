<?php

namespace App\Repository\ogm;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

trait OgmCommonMethod
{
    public function find($id) : ?object{
        if(is_string($id)){
            $id = Uuid::fromString($id);
        }
        if(is_int($id)){
            $id = Uuid::fromInteger($id);
        }
        if(!$id instanceof UuidInterface){
            throw new \Exception("id must be string or integer or UuidInterface");
        }
        return $this->findOneById((int) $id->getInteger()->toString());
    }
}
