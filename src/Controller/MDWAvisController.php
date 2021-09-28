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
            //si un user a un message d'erreur, c'est qu'il a bricole le front
            if($form_avis->isValid()) {
                $this->entityManager->persist($avis);
                $this->entityManager->flush();
                $this->addFlash('confirmation_avis', 'Votre avis a été enregistré. Merci de votre participation');
            } else {
                $this->addFlash('erreur_avis', 'Erreur lors de la soumission de votre avis: un des champs est incorrect');
            }

            return $this->redirectToRoute('vue_produit', ['nom_produit' => $produit->getNom()]);
        }

        return $this->render('avis/form_avis.html.twig', [
            'form_avis' => $form_avis->createView(),
        ]);
    }
}
