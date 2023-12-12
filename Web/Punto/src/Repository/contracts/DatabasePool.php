<?php

namespace App\Repository\contracts;

use App\Entity\Party;
use App\Entity\Player;
use App\Repository\orm\PlayerRepository;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;

class DatabasePool
{
    public function __construct(
        private DocumentManager        $documentManager,
        private EntityManagerInterface $mysqlEntityManager,
        private EntityManagerInterface $sqliteEntityManager,
        private \GraphAware\Neo4j\OGM\EntityManagerInterface $neo4jEntityManager,
        private ManagerRegistry $registry
    ) { }

    public function getObjectManager(DatabaseTypes $types) : ObjectManager{
        switch ($types) {
            case DatabaseTypes::MYSQL:
                return $this->mysqlEntityManager;
            case DatabaseTypes::MONGODB:
                return $this->documentManager;
            case DatabaseTypes::SQLITE:
                return $this->sqliteEntityManager;
            case DatabaseTypes::NEO4J:
                return $this->neo4jEntityManager;
            default:
                throw new \AssertionError("Not implemented");
        }
    }

    public function truncateAll(DatabaseTypes $types) : void {
        $mysqlTables = ["cell", "party", "party_players", "player", "player_card", "round"];
        switch ($types){
            case DatabaseTypes::MYSQL:
                $connection = $this->mysqlEntityManager->getConnection();
                $platform = $connection->getDatabasePlatform();
                $connection->executeQuery('SET FOREIGN_KEY_CHECKS = 0;');
                foreach ($mysqlTables as $name) {
                    $connection->executeUpdate($platform->getTruncateTableSQL($name, true));
                }
                $connection->executeQuery('SET FOREIGN_KEY_CHECKS = 1;');
                break;
            case DatabaseTypes::MONGODB:
                $this->documentManager->getSchemaManager()->dropCollections();
                $this->documentManager->getSchemaManager()->createCollections();
                break;
            case DatabaseTypes::SQLITE:
                $connection = $this->sqliteEntityManager->getConnection();
                $platform = $connection->getDatabasePlatform();
                $connection->executeQuery('PRAGMA foreign_keys = OFF;');
                foreach ($mysqlTables as $name) {
                    $connection->executeUpdate($platform->getTruncateTableSQL($name, true));
                }
                $connection->executeQuery('PRAGMA foreign_keys = ON;');
                break;
        }
    }

    public function getPartyRepository(DatabaseTypes $types) : PartyRepositoryInterface {
        $repo = $this->getObjectManager($types)->getRepository(Party::class);
        assert($repo instanceof PartyRepositoryInterface);
        return $repo;
    }

    public function getPlayerRepository(DatabaseTypes $types) : PlayerRepositoryInterface {
        $repo = $this->getObjectManager($types)->getRepository(Player::class);
        assert($repo instanceof PlayerRepositoryInterface);
        return $repo;
    }

    public function tickDatabases() : void {
        $this->mysqlEntityManager->createQuery("SELECT p FROM App\Entity\Party p")->getResult();
        $this->sqliteEntityManager->createQuery("SELECT p FROM App\Entity\Party p")->getResult();
        $this->documentManager->createQueryBuilder(Party::class)->getQuery()->execute();
    }
}
