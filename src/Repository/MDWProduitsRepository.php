<?php

namespace App\Repository;

use App\Entity\MDWProduits;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MDWProduits|null find($id, $lockMode = null, $lockVersion = null)
 * @method MDWProduits|null findOneBy(array $criteria, array $orderBy = null)
 * @method MDWProduits[]    findAll()
 * @method MDWProduits[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MDWProduitsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MDWProduits::class);
    }

    public function getMisEnAvant($quantite_max) {
        return $this->createQueryBuilder('p')
            ->where("p.mis_en_avant = 1")
            ->andWhere("p.est_visible = 1")
            ->andWhere('p.quantite_stock >= p.limite_basse_stock OR p.commandable_sans_stock = 1')
            ->orderBy('p.date_creation', 'DESC')
            ->setMaxResults($quantite_max)
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return MDWProduits[] Returns an array of MDWProduits objects
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
    public function findOneBySomeField($value): ?MDWProduits
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
