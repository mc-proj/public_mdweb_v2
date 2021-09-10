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

    #[Route('/{nom_produit}', name: 'vue_produit')]
    public function vueProduit($nom_produit): Response {
        
        //dd($nom_produit);
        $produit = $this->MDWProduitsRepository->findOneBy(["nom" => $nom_produit]);
        //dd($produit);

        return $this->render('mdw_produits/detail.html.twig', [
            'produit' => $produit,
        ]);
    }

    // #[Route('/{categorie}/{sous_categorie}/{produit}', name: 'produits_par_categorie')]
    // public function filtreCategorie($categorie, $sous_categorie=null, $produit=null): Response {
        
    //     $recu = [$categorie, $sous_categorie, $produit];
    //     dd($recu);

    //     return $this->render('mdw_produits/index.html.twig', [
    //         'controller_name' => 'MDWProduitsController',
    //     ]);
    // }
}
