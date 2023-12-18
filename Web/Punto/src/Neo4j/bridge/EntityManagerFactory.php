<?php

namespace App\Neo4j\bridge;

use GraphAware\Neo4j\OGM\EntityManager;
use GraphAware\Neo4j\OGM\EntityManagerInterface;

class EntityManagerFactory
{
    public function create() : EntityManagerInterface {
        $em = EntityManager::create($_ENV["NEO4J_URL"]);
        return $em;
    }
}
