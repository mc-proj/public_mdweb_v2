<?php

namespace App\Repository;

use App\Entity\MDWCodesPromos;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MDWCodesPromos|null find($id, $lockMode = null, $lockVersion = null)
 * @method MDWCodesPromos|null findOneBy(array $criteria, array $orderBy = null)
 * @method MDWCodesPromos[]    findAll()
 * @method MDWCodesPromos[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MDWCodesPromosRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MDWCodesPromos::class);
    }

    // /**
    //  * @return MDWCodesPromos[] Returns an array of MDWCodesPromos objects
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
    public function findOneBySomeField($value): ?MDWCodesPromos
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
