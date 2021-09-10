<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\MDWProduitsRepository;

#[Route('/')]

class AccueilController extends AbstractController
{
    private $MDWProduitsRepository;

    public function __construct(MDWProduitsRepository $MDWProduitsRepository) {
        $this->MDWProduitsRepository = $MDWProduitsRepository;
    }

    #[Route('/', name: 'accueil')]
    public function index(): Response
    {
        $produits_recents = $this->MDWProduitsRepository->findBy(array(),
                                                                array("date_creation" => "DESC"),
                                                                6
                                                            );
        $produits_en_avant = $this->MDWProduitsRepository->getMisEnAvant(6);

        return $this->render('accueil/index.html.twig', [
            'produits_recents' => $produits_recents,
            'meilleurs_produits' => $produits_en_avant,
        ]);
    }
}
