<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\MDWAvis;
use App\Form\AvisType;
use App\Repository\MDWProduitsRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/avis')]

class MDWAvisController extends AbstractController
{
    private $produitsRepository;
    private $entityManager;

    public function __construct(MDWProduitsRepository $produitsRepository,
                                EntityManagerInterface $entityManager) {
        $this->produitsRepository = $produitsRepository;
        $this->entityManager = $entityManager;
    }

    #[Route('/formulaire/{id_produit}', name: 'form_avis', requirements: ['id_produit' => '\d+'])]
    public function formAvis(Request $request, int $id_produit): Response
    {
        $avis = new MDWAvis();
        $produit = $this->produitsRepository->findOneBy(["id" => $id_produit]);

        if($produit === null) {//redirection vers 'produit introuvable' + menu recherche
            return $this->redirectToRoute('vue_produit', ['nom_produit' => 0]);
        }

        $avis->setProduit($produit);
        $avis->setUser($this->getUser());
        $form_avis = $this->createForm(AvisType::class, $avis, [
            'action' => $this->generateUrl('form_avis', ['id_produit' => $produit->getId()]) //par defaut, route utilisee est celle de la page qui fait l'include
        ]);
        $form_avis->handleRequest($request);

        if($form_avis->isSubmitted()) {
            //pas d'utilisation des messages d'erreur sur le formulaire
            //les contraintes (note != blank et avis < 255 caracteres) sont imposees via front
            //les meme contraintes sont presentes en back pour la secu
            if($form_avis->isValid()) {
                $this->entityManager->persist($avis);
                $this->entityManager->flush();
                return new JsonResponse(null);
            }

            $response = new JsonResponse([
                'output' => $this->renderView('form/avis_produit.html.twig', [
                    'form_avis' => $form_avis->createView(),
                ])
            ]
            , 200);
           
            return $response;
        }

        return $this->render('form/avis_produit.html.twig', [
            'form_avis' => $form_avis->createView(),
        ]);
    }
}