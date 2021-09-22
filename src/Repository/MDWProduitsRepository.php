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
            //->andWhere('p.quantite_stock >= p.limite_basse_stock OR p.commandable_sans_stock = 1')
            ->orderBy('p.date_creation', 'DESC')
            ->setMaxResults($quantite_max)
            ->getQuery()
            ->getResult();
    }

    public function getByNomOrId($data) {
        return $this->createQueryBuilder('p')
            ->where("p.id = :data")
            ->orWhere("p.nom = :data")
            ->andWhere("p.est_visible = 1")
            //->andWhere('p.quantite_stock >= p.limite_basse_stock OR p.commandable_sans_stock = 1')
            ->setParameter('data', $data)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    //original
    /*public function getByCategories($categorie, $sous_categorie=null, $nom_produit=null) {

        $requete = $this->createQueryBuilder('p')
                        ->innerJoin('p.categories', 'c')
                        ->where('c.nom = :categorie')
                        ->setParameter('categorie', $categorie)
                        ->andWhere("p.est_visible = 1");

        if($sous_categorie !== null) {
            $requete->innerJoin('p.categories', 'sc')
                    ->andWhere('sc.nom = :sous_categorie')
                    ->setParameter('sous_categorie', $sous_categorie);
        }

        if($nom_produit !== null) {
            $requete->andWhere('p.nom = :nom_produit')
                    ->setParameter('nom_produit', $nom_produit);
        }

        return $requete->getQuery()
            ->getResult();
    }*/

    public function getByCategories($categorie, $sous_categorie=null, $nom_produit=null, $rang_min=null, $quantite=null, $tri=null) {

        $champ_tri = 'p.date_creation';
        $type_tri = 'DESC';

        switch($tri) {
            case 'ancien':
                $type_tri = 'ASC';
                break;

            case 'croissant':
                $champ_tri = 'p.tarif';
                $type_tri = 'ASC';
                break;

            case 'decroissant':
                $champ_tri = 'p.tarif';
                $type_tri = 'DESC';
                break;
        }

        $requete = $this->createQueryBuilder('p')
                        ->innerJoin('p.categories', 'c')
                        ->where('c.nom = :categorie')
                        ->setParameter('categorie', $categorie)
                        ->andWhere("p.est_visible = 1");

        if($sous_categorie !== null) {
            $requete->innerJoin('p.categories', 'sc')
                    ->andWhere('sc.nom = :sous_categorie')
                    ->setParameter('sous_categorie', $sous_categorie);
        }

        if($nom_produit !== null) {
            $requete->andWhere('p.nom = :nom_produit')
                    ->setParameter('nom_produit', $nom_produit);
        }

        if($rang_min !== null) {
            $requete->setFirstResult($rang_min);
        }

        if($quantite !== null) {
            $requete->setMaxResults($quantite);
        }

        /*
            ->setFirstResult($rang_min)

            //->orderBy($champ_tri, $type_tri)
            //->orderBy('p.date_creation', 'DESC')
            //->orderBy(':champ', ':tri')
            //->setParameter('champ', $champ_tri)
            //->setParameter('tri', $champ_tri)

            ->setMaxResults($quantite)
            ->addOrderBy($champ_tri, $type_tri)
        */

        return $requete->addOrderBy($champ_tri, $type_tri)
            ->getQuery()
            ->getResult();
    }

    public function findByBegin($debut) {

        return $this->createQueryBuilder('p')
            ->where('p.est_visible = 1')
            ->leftJoin('p.categories', 'c')
            ->leftJoin('c.sous_categories', 'sc')
            ->andWhere('p.nom LIKE :debut')
            ->setParameter('debut', $debut.'%')
            ->select('p.nom AS nom_produit, c.nom AS categorie, sc.nom AS sous_categorie')
            ->groupBy('nom_produit')
            ->orderBy('nom_produit', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function globalFindByBegin($debut) {
        return $this->createQueryBuilder('p')
            ->where('p.est_visible = 1')
            ->leftJoin('p.categories', 'c')
            ->leftJoin('c.sous_categories', 'sc')
            ->andWhere('p.nom LIKE :debut')
            ->orWhere('c.nom LIKE :debut')
            ->orWhere('sc.nom LIKE :debut')
            ->setParameter('debut', $debut.'%')
            ->groupBy('p.nom')
            ->orderBy('p.nom', 'DESC')
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
