<?php

namespace App\Repository;

use App\Entity\MDWPaniersProduits;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MDWPaniersProduits|null find($id, $lockMode = null, $lockVersion = null)
 * @method MDWPaniersProduits|null findOneBy(array $criteria, array $orderBy = null)
 * @method MDWPaniersProduits[]    findAll()
 * @method MDWPaniersProduits[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MDWPaniersProduitsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MDWPaniersProduits::class);
    }

    // /**
    //  * @return MDWPaniersProduits[] Returns an array of MDWPaniersProduits objects
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
    public function findOneBySomeField($value): ?MDWPaniersProduits
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
