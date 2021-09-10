<?php

namespace App\Repository;

use App\Entity\MDWCodesPromosUsers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MDWCodesPromosUsers|null find($id, $lockMode = null, $lockVersion = null)
 * @method MDWCodesPromosUsers|null findOneBy(array $criteria, array $orderBy = null)
 * @method MDWCodesPromosUsers[]    findAll()
 * @method MDWCodesPromosUsers[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MDWCodesPromosUsersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MDWCodesPromosUsers::class);
    }

    // /**
    //  * @return MDWCodesPromosUsers[] Returns an array of MDWCodesPromosUsers objects
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
    public function findOneBySomeField($value): ?MDWCodesPromosUsers
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
