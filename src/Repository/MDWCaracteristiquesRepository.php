<?php

namespace App\Repository;

use App\Entity\MDWCaracteristiques;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MDWCaracteristiques|null find($id, $lockMode = null, $lockVersion = null)
 * @method MDWCaracteristiques|null findOneBy(array $criteria, array $orderBy = null)
 * @method MDWCaracteristiques[]    findAll()
 * @method MDWCaracteristiques[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MDWCaracteristiquesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MDWCaracteristiques::class);
    }

    // /**
    //  * @return MDWCaracteristiques[] Returns an array of MDWCaracteristiques objects
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
    public function findOneBySomeField($value): ?MDWCaracteristiques
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
