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

        /*
        $facture = $this->facturesRepository->findOneBy([
            "id" => 1,
        ]);
        dd($facture);  //! retourne 1 factureProduit (pivot)
        */

        if($this->getUser()) {
            $facture = $this->facturesRepository->findOneBy([
                "id" => $request->request->get("facture_id"),
                //"id" => 1,
                "user" => $this->getUser(),
            ]);

            // dd($facture);

            $facture = $normalizer->normalize($facture, 'json',  ['groups' => 'read:facture:MDWFacture']);
            //$facture = $normalizer->normalize($facture);
            $response = json_encode($facture);
            $response = new JsonResponse($response);
            //dd($response);
            return $response;
        }

        return $this->redirectToRoute('app_login');

        //if $this->get_current_user //secu user connecte
        //get facture by facture_id && user_id  //secu anti petit malin qui enverrai id d'une facture d'un autre client

        /*
$facture_id = $request->request->get("facture_id");
        $facture = $this->facturesRepository->findOneBy(["id" => $facture_id]);

        $response = json_encode($facture);
        $response = new JsonResponse($response);
        return $response;

        */


        /*return $this->render('mdw_factures/index.html.twig', [  //cette vue sera probablement a supprimer
            'controller_name' => 'MDWFacturesController',
        ]);*/
    }
}
