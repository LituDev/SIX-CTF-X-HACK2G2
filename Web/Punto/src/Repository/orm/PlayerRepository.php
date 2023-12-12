<?php

namespace App\Repository\orm;

use App\Entity\Player;
use App\Repository\contracts\PlayerRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @extends ServiceEntityRepository<Player>
 *
 * @method Player|null find($id, $lockMode = null, $lockVersion = null)
 * @method Player|null findOneBy(array $criteria, array $orderBy = null)
 * @method Player[]    findAll()
 * @method Player[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlayerRepository extends EntityRepository implements PlayerRepositoryInterface
{
    public function getPlayer(UuidInterface|string $id): ?Player
    {
        if(is_string($id)){
            $id = Uuid::fromString($id);
        }
        return $this->find($id);
    }
}
