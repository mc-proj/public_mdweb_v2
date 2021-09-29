<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\MDWPaniersRepository;

#[Route('/paniers')]

class MDWPaniersController extends AbstractController
{
    private $paniersRepository;

    public function __construct(MDWPaniersRepository $paniersRepository) {
        $this->paniersRepository = $paniersRepository;
    }

    #[Route('/', name: 'accueil_panier')]
    public function index(): Response
    {
        $panier = $this->paniersRepository->findOneBy(['id' => 0]);
        //dd($panier); //bien null si rien trouve

        return $this->render('mdw_paniers/index.html.twig', [
            //'controller_name' => 'MDWPaniersController',
            'panier' => $panier, //provi
        ]);
    }
}
