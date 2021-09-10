<?php

namespace App\Repository;

use App\Entity\MDWAvis;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MDWAvis|null find($id, $lockMode = null, $lockVersion = null)
 * @method MDWAvis|null findOneBy(array $criteria, array $orderBy = null)
 * @method MDWAvis[]    findAll()
 * @method MDWAvis[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MDWAvisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MDWAvis::class);
    }

    // /**
    //  * @return MDWAvis[] Returns an array of MDWAvis objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MDWAvis
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
