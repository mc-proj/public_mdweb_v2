<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\MDWFacturesRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use DateTime;

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

            $date_achat = $facture->getDateCreation(); //test

            $facture = $normalizer->normalize($facture, 'json',  ['groups' => 'read:facture:MDWFacture']); //ori

            //test begin
            //dd($facture);
            $factures_produits = $facture['produit'];
            //dd($factures_produits);
            $factures_produits_edite = [];

            foreach($factures_produits as $facture_produit) {
                //dd($facture_produit);
                $produit = $facture_produit['produit'];
                $infos_promo = [
                    'en_promo' => false,
                    'tarif_actif' => $produit['tarif']
                ];

                if(new DateTime($produit['date_debut_promo']) <= $date_achat && new DateTime($produit['date_fin_promo']) >= $date_achat) {
                    $infos_promo['en_promo'] = true;
                    $infos_promo['tarif_actif'] = $produit['tarif_promo'];
                }

                $produit = array_merge($produit, $infos_promo);
                $facture_produit['produit'] = $produit;

                //dd($facture_produit['produit']); //array ac infos produit en cours
                //dd($facture_produit);  //entite facture_produit ac les infos produit editees OK
                array_push($factures_produits_edite, $facture_produit);
            }

            //dd($factures_produits_edite); // OK comporte bien les infos voulues
            $facture['produit'] = $factures_produits_edite;

            //dd($facture); //OK

            //test end

            $response = json_encode($facture);
            $response = new JsonResponse($response);
            return $response;
        }

        return $this->redirectToRoute('app_login');
    }
}
