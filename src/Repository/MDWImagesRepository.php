<?php

namespace App\Repository;

use App\Entity\MDWImages;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MDWImages|null find($id, $lockMode = null, $lockVersion = null)
 * @method MDWImages|null findOneBy(array $criteria, array $orderBy = null)
 * @method MDWImages[]    findAll()
 * @method MDWImages[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MDWImagesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MDWImages::class);
    }

    // /**
    //  * @return MDWImages[] Returns an array of MDWImages objects
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
    public function findOneBySomeField($value): ?MDWImages
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
