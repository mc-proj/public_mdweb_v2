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

    public function getByCategories($categorie, $sous_categorie=null, $nom_produit=null) {

        //dd($categorie . '|' . $sous_categorie . '|' . $nom_produit);
        //"categorie 1|sous_categorie 1|produit_6" OK

        //essai v1
        /*$requete = $this->createQueryBuilder('p')
                ->where("p.est_visible = 1")
                ->leftJoin('p.categories', 'c')
                ->andWhere('c.nom = :categorie')
                ->setParameter('categorie', $categorie);

        if($sous_categorie !== null) {
            $requete->leftJoin('c.sous_categories', 'sc')
                    ->andWhere('sc.nom = :sous_categorie')
                    ->setParameter('sous_categorie', $sous_categorie);

            if($nom_produit !== null) {
                $requete->andWhere('p.nom = :nom_produit')
                        ->setParameter('nom_produit', $nom_produit);
            }
        }*/

        //essai v2
        $requete = $this->createQueryBuilder('p')
                /*->where('p.nom = :nom_produit')
                        ->setParameter('nom_produit', $nom_produit)*/

                //->where("p.est_visible = 1")
                /*->leftJoin('p.categories', 'c')
                ->where('c.nom = :categorie')
                ->setParameter('categorie', $categorie);*/

                //nope
                /*->andWhere('c.nom = :sous_categorie')
                ->setParameter('sous_categorie', $sous_categorie);*/

                ///piste 1
                //NOPE: avec un AND -> on veux une categorie qui a 2 valeur simultanement, un OR ne correspond pas a ce qu'on veux
                /*->leftJoin('p.categories', 'c')
                ->where('c.nom = :categorie OR c.nom = :sous_categorie')
                ->setParameter('categorie', $categorie)
                ->setParameter('sous_categorie', $sous_categorie);*/
                /////fin piste 1

                //piste 2  --> return []
                ->leftJoin('p.categories', 'c')
                ->where('c.nom = :categorie')
                ->setParameter('categorie', $categorie)
                ->leftJoin('c.sous_categories', 'sc')
                ->andWhere('sc.nom = :sous_categorie')
                ->setParameter('sous_categorie', $sous_categorie);
                //fin piste 2

                //nope
                /*->leftJoin('c.sous_categories', 'sc')
                ->orWhere('sc.nom = :sous_categorie')
                ->setParameter('sous_categorie', $sous_categorie);*/

                //nope
                /*->andWhere('c.nom = :sous_categorie')
                ->setParameter('sous_categorie', $sous_categorie);*/

                

        /*if($sous_categorie !== null) {
            $requete->leftJoin('c.sous_categories', 'sc')
                    ->andWhere('sc.nom = :sous_categorie')
                    ->setParameter('sous_categorie', $sous_categorie);

            if($nom_produit !== null) {
                $requete->andWhere('p.nom = :nom_produit')
                        ->setParameter('nom_produit', $nom_produit);
            }
        }*/
        

        //--fin xp zone

        return $requete->getQuery()
            ->getResult();

        /*return $this->createQueryBuilder('p')
            ->where("p.est_visible = 1")
            ->leftJoin('p.categories', 'c')
            ->where('c.nom = :categorie')
            ->setParameter('categorie', $categorie)
            //->andWhere('p.quantite_stock >= p.limite_basse_stock OR p.commandable_sans_stock = 1')
            //->orderBy('p.date_creation', 'DESC')
            //->setMaxResults($quantite_max)

            ->leftJoin('c.sous_categories', 'sc')
            ->andWhere('sc.nom = :sous_categorie')
            ->setParameter('sous_categorie', $sous_categorie)


            ->getQuery()
            ->getResult();*/

            /*
->leftJoin('p.categories', 'c')
            ->where('c.nom = :categorie')
            ->setParameter('categorie', $categorie)
            ->leftJoin('c.sous_categories', 'sc')
            ->andWhere('sc.nom = :sous_categorie')
            ->setParameter('sous_categorie', $sous_categorie)
            */
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
