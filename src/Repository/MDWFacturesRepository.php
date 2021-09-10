<?php

namespace App\Repository;

use App\Entity\MDWFactures;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MDWFactures|null find($id, $lockMode = null, $lockVersion = null)
 * @method MDWFactures|null findOneBy(array $criteria, array $orderBy = null)
 * @method MDWFactures[]    findAll()
 * @method MDWFactures[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MDWFacturesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MDWFactures::class);
    }

    // /**
    //  * @return MDWFactures[] Returns an array of MDWFactures objects
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
    public function findOneBySomeField($value): ?MDWFactures
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
