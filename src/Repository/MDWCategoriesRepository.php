<?php

namespace App\Repository;

use App\Entity\MDWCategories;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MDWCategories|null find($id, $lockMode = null, $lockVersion = null)
 * @method MDWCategories|null findOneBy(array $criteria, array $orderBy = null)
 * @method MDWCategories[]    findAll()
 * @method MDWCategories[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MDWCategoriesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MDWCategories::class);
    }

    public function getMainCategories() {
        return $this->createQueryBuilder('c')
                    ->leftJoin('c.categories_parentes', 'cp')
                    ->where('cp.nom is null')
                    ->getQuery()
                    ->getResult();
    }

    // /**
    //  * @return MDWCategories[] Returns an array of MDWCategories objects
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
    public function findOneBySomeField($value): ?MDWCategories
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
