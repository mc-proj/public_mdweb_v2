<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\MDWProduitsRepository;
use App\Repository\MDWCategoriesRepository;

#[Route('/produits')]

class MDWProduitsController extends AbstractController
{
    private $MDWProduitsRepository;
    private $MDWCategoriesRepository;

    public function __construct(MDWProduitsRepository $MDWProduitsRepository,
                                MDWCategoriesRepository $MDWCategoriesRepository) {
        $this->MDWProduitsRepository = $MDWProduitsRepository;
        $this->MDWCategoriesRepository = $MDWCategoriesRepository;
    }

    #[Route('/', name: 'categories')]
    public function vueBoutique(): Response {

        $categories = $this->MDWCategoriesRepository->getMainCategories();

        return $this->render('mdw_produits/boutique.html.twig', [
            'categories' => $categories
        ]);
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

        //$tri_date = 'DESC';
        //$recu = [$categorie, $sous_categorie, $nom_produit];

        $produits = $this->MDWProduitsRepository->getByCategories($categorie, $sous_categorie, $nom_produit);
        

        if($nom_produit !== null && count($produits) > 0) {
            return $this->render('mdw_produits/detail.html.twig', [
                'produit' => $produits[0],
                'categorie' => $categorie,
                'sous_categorie' => $sous_categorie,
            ]);
        }


        $quantite_totale = count($produits);
        
        
        //en attendant de faire mieux  -- WORKS
        /*$copie_partielle = [];
        if($quantite_totale > 6) {  //16
            for($i=0; $i<6; $i) {  //16
                array_push($copie_partielle, $produits[$i]);
            }
            $produits = $copie_partielle;
        }
        dd($produits);*/
        
        return $this->render('mdw_produits/categorie.html.twig', [
            'produits' => $produits,
            'quantite_totale' => $quantite_totale,
            'categorie' => $categorie,
            'sous_categorie' => $sous_categorie,
        ]);
    }
}
