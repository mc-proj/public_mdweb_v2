<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Repository\MDWUsersRepository;
use Doctrine\ORM\EntityManagerInterface;

#[AsCommand(
    name: 'CleanVisiteurs',
    description: 'Efface les users/visiteurs inactifs depuis au moins 2 jours',
)]
class CleanVisiteursCommand extends Command
{
    private $usersRepository;

    public function __construct(MDWUsersRepository $usersRepository,
                                EntityManagerInterface $entityManager) {
        $this->usersRepository = $usersRepository;
        $this->entityManager = $entityManager;
        parent::__construct();
    }


    protected function configure(): void
    {
        /*$this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;*/
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $users = $this->usersRepository->getOldGuest("P2D");//Period of 2 Days
        $old_guests = [];

        foreach($users as $user) {
            if(in_array("ROLE_GUEST", $user->getRoles())) {
                array_push($old_guests, $user);
            }
        }

        foreach($old_guests as $old_guest) {
            $panier = $old_guest->getPanier();

            if($panier === null) {
                $this->entityManager->remove($old_guest);
            } else if($panier->isOld("P2D")) {
                $paniers_user = $panier->getProduits();

                foreach($paniers_user as $panier_user) {
                    $this->entityManager->remove($panier_user);
                }

                $this->entityManager->remove($panier);
                $this->entityManager->remove($old_guest);
            }

            $this->entityManager->flush();
        }

        $io->success('nettoyage visiteurs OK');
        return Command::SUCCESS;
    }
}
