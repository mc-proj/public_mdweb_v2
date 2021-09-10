<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\MDWImages;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use App\Repository\MDWProduitsRepository;


class MDWImagesFixtures extends Fixture implements DependentFixtureInterface
{
    private $produitRepository;

    public function __construct(MDWProduitsRepository $produitRepository) {
        $this->produitRepository = $produitRepository;
    }

    public function getDependencies()
    {
        return [
            MDWProduitsFixtures::class,
        ];
    }

    public function load(ObjectManager $manager)
    {
        $produits = $this->produitRepository->findAll();

        for($i=0;$i<40;$i++) {
            $image = new MDWImages();

            if($i<10) {
                $image->setImage("bot.jpg");
            } else if($i<20) {
                $image->setImage("clavier_laser.jpg");
            } else if($i<30) {
                $image->setImage("souris.jpg");
            } else {
                $image->setImage("tel.jpg");
            }

            $indice = $i;
            if(($indice > 9) && ($indice < 20)) {
                $indice -= 10;
            } else if(($indice >= 20) && ($indice < 30)) {
                $indice -= 20;
            } else if($indice >= 30) {
                $indice -= 30;
            }

            $image->setProduit($produits[$indice]);
            $manager->persist($image);
        }

        $manager->flush();
    }
}
