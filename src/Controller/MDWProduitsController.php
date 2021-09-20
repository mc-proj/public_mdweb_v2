<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\MDWProduitsRepository;
use App\Repository\MDWCategoriesRepository;
use App\Form\RechercheStandardType;
use Symfony\Component\HttpFoundation\Request;

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

    #[Route('/recherche', name: 'recherche_standard')]
    public function rechercheStandard(Request $request): Response {

        $form = $this->createForm(RechercheStandardType::class, null, [
            'action' => $this->generateUrl('recherche_standard') //par defaut, route utilisee est celle de la page qui fait l'include
            //!!! 2 includes faits 1 ds produits/categories et 1 ds produits/details
        ]);
        $form->handleRequest($request);

        //dd("passe");  //ok chargement + sousmission form
        /*$data = $form->getData();
        dd($data);*/

        /*
        test faille xss
        txt ds champ => <script>alert('C\'est une faille XSS qu\'on a là')</script> -> aucune execution :)
        */

        if($form->isSubmitted() /*&& $form->isValid()*/) {
            //dd("submitted");

            //dd($form->getData()["recherche"]);
            //!! marche ok avec texte precis -- NOK si texte partiel (genre 'prod' au lieu 'produit_6')

            //faire une requete perso ac un like
            //on ne sait pas d'avance si le param soumis est la categorie pu la sous categorie pu le nom produit
            //solution basique -> enchainement max de 3 requetes perso (passe a requete n+1 si resultat n === 0)
            //faire like simultane sur les 3 champs -> permet recherche la + large pr user

            $produits = $this->MDWProduitsRepository->globalFindByBegin($form->getData()["recherche"]);
            //dd($produits);

            return $this->render('mdw_produits/resultats_recherche.html.twig', [
                'produits' => $produits,
                'recherche' => $form->getData()["recherche"],
            ]);

            //return $this->redirectToRoute('produits_par_categorie', ['categorie' => $form->getData()["recherche"]]);

            /*
            $data = $form->getData();
            $produits = $this->produitsRepository->rechercheGenerale($data["recherche"]);

            return $this->render('produits/resultat_recherche.html.twig', [
                'recherche' => $data["recherche"],
                'produits' => $produits
            ]);
            */
        }


        return $this->render('mdw_produits/recherche.html.twig', [
            'form_recherche' => $form->createView()
        ]);
    }

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
        $quantite_totale = count($produits);

        //notes tests begin
        /* methode getByCategories modifiee pr pouvoir prendre en compte rang min et qte max resultats a retourner
        //a ce niveau, on a besoin du nb total de produits correspondants
        //faire une recherche sans limite (comme de base) --> recup nb total resultats
        //puis faire une copie partielle des resultats pr affichage
        */

        if($quantite_totale !== 0) {
            dd($produits[0]);  //works fine (pre test pr copie partielle)
            //array_chunk -- https://www.php.net/manual/fr/function.array-chunk.php
        }
        //notes tests end

        if($quantite_totale === 0 && $nom_produit === null) {
            if($sous_categorie !== null) {  //cas url /categorie/nom_produit
                return $this->redirectToRoute('vue_produit', ['nom_produit' => $sous_categorie]);
            }
            return $this->redirectToRoute('vue_produit', ['nom_produit' => $categorie]); // ! cas ou seul 1er param fourni, correspond a un nom de produit
        }
        //----

        if($nom_produit !== null && count($produits) > 0) {
            return $this->render('mdw_produits/detail.html.twig', [
                'produit' => $produits[0],
                'categorie' => $categorie,
                'sous_categorie' => $sous_categorie,
            ]);
        }


        
        
        
        //en attendant de faire mieux  -- WORKS
        /*$copie_partielle = [];
        if($quantite_totale > 6) {  //16
            for($i=0; $i<6; $i) {  //16
                array_push($copie_partielle, $produits[$i]);
            }
            $produits = $copie_partielle;
        }
        dd($produits);*/

        //test v2
        /*if($quantite_totale > 0) {
            $copie = [$produits[0], $produits[1], $produits[2]];
            $produits = $copie;
        }*/
        //fin test v2
        
        return $this->render('mdw_produits/categorie.html.twig', [
            'produits' => $produits,
            'quantite_totale' => $quantite_totale,
            'categorie' => $categorie,
            'sous_categorie' => $sous_categorie,
        ]);
    }
}
