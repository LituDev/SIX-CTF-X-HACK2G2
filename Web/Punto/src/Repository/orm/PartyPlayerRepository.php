<?php

namespace App\Repository\orm;

use App\Entity\PartyPlayer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PartyPlayer>
 *
 * @method PartyPlayer|null find($id, $lockMode = null, $lockVersion = null)
 * @method PartyPlayer|null findOneBy(array $criteria, array $orderBy = null)
 * @method PartyPlayer[]    findAll()
 * @method PartyPlayer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PartyPlayerRepository extends EntityRepository
{
}
