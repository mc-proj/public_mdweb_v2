<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\MDWProduitsRepository;

#[Route('/produits')]

class MDWProduitsController extends AbstractController
{
    private $MDWProduitsRepository;

    public function __construct(MDWProduitsRepository $MDWProduitsRepository) {
        $this->MDWProduitsRepository = $MDWProduitsRepository;
    }
    //@TODO : route /nb_id ac requirement integer + route /categorie/sous_categorie/produit requirements string
    //nb_id recherche produit par id puis redirige vers autre methode
    // #[Route('/m/d/w/produits', name: 'm_d_w_produits')]
    // public function index(): Response
    // {
    //     return $this->render('mdw_produits/index.html.twig', [
    //         'controller_name' => 'MDWProduitsController',
    //     ]);
    // }

    //Route("/blog/{page}", name="blog_list", requirements={"page"="\d+"})
    //[0-9a-zA-Z_]

    #[Route('/details/{nom_produit}', name: 'vue_produit')] //recherche generale par nom ou id
    public function vueProduit($nom_produit): Response {

        $produit = $this->MDWProduitsRepository->getByNomOrId($nom_produit);
        $categorie_principale = "";
        $categorie_secondaire = "";

        if($produit !== null) {
            foreach($produit->getCategories() as $categorie) {
                if(count($categorie->getCategoriesParentes()) > 0) {
                    $categorie_secondaire = $categorie->getNom();
    
                } else {
                    $categorie_principale = $categorie->getNom();
                }
    
                if($categorie_principale !== '' && $categorie_secondaire !== '') {
                    break;
                }
            }
        }

        return $this->render('mdw_produits/detail.html.twig', [
            'produit' => $produit,
            'categorie' => $categorie_principale,
            'sous_categorie' => $categorie_secondaire,
        ]);
    }

    #[Route('/filtre/{categorie}/{sous_categorie}/{nom_produit}', name: 'produits_par_categorie')]
    public function filtreCategorie($categorie, $sous_categorie=null, $nom_produit=null): Response {

        /*$tri_date = 'DESC';

        if($nom_produit === null) {
            if($sous_categorie === null) {                
                $produits = $this->MDWProduitsRepository->findBy(
                    ['categories' => 'categorie_1'],
                    //array('categories' => $categorie),
                    //array('date_creation' => $tri_date)
                );
            }
        }*/

        /*$produits = $this->MDWProduitsRepository->findBy(
            array('categories' => $categorie),
            array('date_creation' => $tri_date)
        );*/

        $recu = [$categorie, $sous_categorie, $nom_produit];
        //dd($recu);

        $produits = $this->MDWProduitsRepository->getByCategories($categorie, $sous_categorie, $nom_produit);

        /*foreach($produits as $produit) {
            $nom_cat = [];
            $categories = $produit->getCategories();
            foreach($categories as $categorie) {
                array_push($nom_cat, $categorie->getNom());
            }

            dd($nom_cat);
        }*/

        dd($produits);
        
        

        return $this->render('mdw_produits/index.html.twig', [
            'controller_name' => 'MDWProduitsController',
        ]);
    }
}
