<?php

namespace App\Repository;

use App\Entity\MDWTauxTva;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MDWTauxTva|null find($id, $lockMode = null, $lockVersion = null)
 * @method MDWTauxTva|null findOneBy(array $criteria, array $orderBy = null)
 * @method MDWTauxTva[]    findAll()
 * @method MDWTauxTva[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MDWTauxTvaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MDWTauxTva::class);
    }

    // /**
    //  * @return MDWTauxTva[] Returns an array of MDWTauxTva objects
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
    public function findOneBySomeField($value): ?MDWTauxTva
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
