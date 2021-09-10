<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\MDWCaracteristiques;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class MDWCaracteristiquesFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies()
    {
        return [
            MDWTypesCaracteristiquesFixtures::class,
            MDWProduitsFixtures::class,
        ];
    }

    public function load(ObjectManager $manager)
    {
        for($i = 0; $i < 10; $i++) {
            $caracteristique = new MDWCaracteristiques();

            if($i < 5) {
                $caracteristique->setValeur("rouge");
            } else {
                $caracteristique->setValeur("vert");
            }
            $caracteristique->setTypeCaracteristique($this->getReference(MDWTypesCaracteristiquesFixtures::TYPE_CARACTERISTIQUE_COULEUR));

            switch($i) {
                case 0:
                    $caracteristique->setProduit($this->getReference(MDWProduitsFixtures::PRODUIT_1));
                    break;

                case 1:
                    $caracteristique->setProduit($this->getReference(MDWProduitsFixtures::PRODUIT_2));
                    break;

                case 2:
                    $caracteristique->setProduit($this->getReference(MDWProduitsFixtures::PRODUIT_3));
                    break;

                case 3:
                    $caracteristique->setProduit($this->getReference(MDWProduitsFixtures::PRODUIT_4));
                    break;

                case 4:
                    $caracteristique->setProduit($this->getReference(MDWProduitsFixtures::PRODUIT_5));
                    break;

                case 5:
                    $caracteristique->setProduit($this->getReference(MDWProduitsFixtures::PRODUIT_6));
                    break;

                case 6:
                    $caracteristique->setProduit($this->getReference(MDWProduitsFixtures::PRODUIT_7));
                    break;

                case 7:
                    $caracteristique->setProduit($this->getReference(MDWProduitsFixtures::PRODUIT_8));
                    break;

                case 8:
                    $caracteristique->setProduit($this->getReference(MDWProduitsFixtures::PRODUIT_9));
                    break;

                case 9:
                    $caracteristique->setProduit($this->getReference(MDWProduitsFixtures::PRODUIT_10));
                    break;
            }

            $manager->persist($caracteristique);
            $manager->flush();
        }

        for($j = 0; $j < 10; $j++) {
            $caracteristique = new MDWCaracteristiques();

            if($j < 5) {
                $caracteristique->setValeur("100 grammes");
            } else {
                $caracteristique->setValeur("200 grammes");
            }
            $caracteristique->setTypeCaracteristique($this->getReference(MDWTypesCaracteristiquesFixtures::TYPE_CARACTERISTIQUE_POIDS));

            switch($j) {
                case 0:
                    $caracteristique->setProduit($this->getReference(MDWProduitsFixtures::PRODUIT_1));
                    break;

                case 1:
                    $caracteristique->setProduit($this->getReference(MDWProduitsFixtures::PRODUIT_2));
                    break;

                case 2:
                    $caracteristique->setProduit($this->getReference(MDWProduitsFixtures::PRODUIT_3));
                    break;

                case 3:
                    $caracteristique->setProduit($this->getReference(MDWProduitsFixtures::PRODUIT_4));
                    break;

                case 4:
                    $caracteristique->setProduit($this->getReference(MDWProduitsFixtures::PRODUIT_5));
                    break;

                case 5:
                    $caracteristique->setProduit($this->getReference(MDWProduitsFixtures::PRODUIT_6));
                    break;

                case 6:
                    $caracteristique->setProduit($this->getReference(MDWProduitsFixtures::PRODUIT_7));
                    break;

                case 7:
                    $caracteristique->setProduit($this->getReference(MDWProduitsFixtures::PRODUIT_8));
                    break;

                case 8:
                    $caracteristique->setProduit($this->getReference(MDWProduitsFixtures::PRODUIT_9));
                    break;

                case 9:
                    $caracteristique->setProduit($this->getReference(MDWProduitsFixtures::PRODUIT_10));
                    break;
            }

            $manager->persist($caracteristique);
            $manager->flush();
        }

    }
}
