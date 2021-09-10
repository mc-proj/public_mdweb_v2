<?php

namespace App\Repository;

use App\Entity\MDWPaniers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MDWPaniers|null find($id, $lockMode = null, $lockVersion = null)
 * @method MDWPaniers|null findOneBy(array $criteria, array $orderBy = null)
 * @method MDWPaniers[]    findAll()
 * @method MDWPaniers[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MDWPaniersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MDWPaniers::class);
    }

    // /**
    //  * @return MDWPaniers[] Returns an array of MDWPaniers objects
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
    public function findOneBySomeField($value): ?MDWPaniers
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
