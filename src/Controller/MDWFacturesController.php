<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\MDWFacturesRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/factures')]

class MDWFacturesController extends AbstractController
{
    private $facturesRepository;

    public function __construct(MDWFacturesRepository $facturesRepository) {
        $this->facturesRepository = $facturesRepository;
    }

    #[Route('/mafacture', name: 'ma_facture', methods: 'POST')]
    public function index(Request $request, NormalizerInterface $normalizer): Response
    {
        if($this->getUser()) {
            $facture = $this->facturesRepository->findOneBy([
                "id" => $request->request->get("facture_id"),
                "user" => $this->getUser(),
            ]);

            $date_achat = $facture->getDateCreation();
            $factures_produits = $facture->getProduit();
            $tarifs = [];

            foreach($factures_produits as $facture_produit) {
                $produit = $facture_produit->getProduit();
                $tarifs[strval($produit->getId())] = $produit->getTarifEffectif($date_achat);
            }

            $facture = $normalizer->normalize($facture, 'json',  ['groups' => 'read:facture:MDWFacture']);
            $facture['tarifs'] = $tarifs;
            $response = json_encode($facture);
            $response = new JsonResponse($response);
            return $response;
        }

        return $this->redirectToRoute('app_login');
    }
}