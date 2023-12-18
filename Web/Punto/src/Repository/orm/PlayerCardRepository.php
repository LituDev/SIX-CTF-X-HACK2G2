<?php

namespace App\Repository\orm;

use App\Entity\Player;
use App\Entity\PlayerCard;
use App\Entity\Round;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PlayerCard>
 *
 * @method PlayerCard|null find($id, $lockMode = null, $lockVersion = null)
 * @method PlayerCard|null findOneBy(array $criteria, array $orderBy = null)
 * @method PlayerCard[]    findAll()
 * @method PlayerCard[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlayerCardRepository extends EntityRepository
{
}
