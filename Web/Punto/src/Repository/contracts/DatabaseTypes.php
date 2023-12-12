<?php

namespace App\Repository\contracts;

enum DatabaseTypes: string
{
    case MYSQL = "mysql";
    case MONGODB = "mongodb";
    case SQLITE = "sqlite";
    case NEO4J = "neo4j";
}
