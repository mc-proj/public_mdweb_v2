<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\MDWProduits;
use DateTime;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class MDWProduitsFixtures extends Fixture implements DependentFixtureInterface
{
    public const PRODUIT_1 = "produit 1";
    public const PRODUIT_2 = "produit 2";
    public const PRODUIT_3 = "produit 3";
    public const PRODUIT_4 = "produit 4";
    public const PRODUIT_5 = "produit 5";
    public const PRODUIT_6 = "produit 6";
    public const PRODUIT_7 = "produit 7";
    public const PRODUIT_8 = "produit 8";
    public const PRODUIT_9 = "produit 9";
    public const PRODUIT_10 = "produit 10";

    public function getDependencies()
    {
        return [
            MDWCategoriesFixtures::class,
            MDWTauxTvaFixtures::class,
        ];
    }

    public function load(ObjectManager $manager)
    {
        for($i = 0; $i < 10; $i++) {
            $produit = new MDWProduits();
            $produit->setReference("ABCDEF" . $i);
            $produit->setNom("produit_" . $i);
            $produit->setEstVisible(true);
            $produit->setDescriptionCourte("description courte produit " . $i);
            $produit->setDescription("description produit " . $i);
            $produit->setDateDebutPromo(new DateTime("2020-07-" . $i));
            $produit->setTvaActive(true);
            $produit->setQuantiteStock(50);
            $produit->setLimiteBasseStock(10);
            $produit->setCommandableSansStock(false);
            $prix = rand(1000, 15000);
            $produit->setTarif($prix);
            $produit->setTarifPromo($prix - ($prix * 10/100));
            $produit->setDateCreation(new DateTime("2020-" . $i+1 . "-01"));

            if($i < 5) {
                $produit->setMisEnAvant(false);
                $produit->setDateFinPromo(new DateTime("2021-01-" . $i));
                $produit->setEstEvaluable(false);
                $produit->setTauxTva($this->getReference(MDWTauxTvaFixtures::TAUX_TVA_1));
                $produit->addCategory($this->getReference(MDWCategoriesFixtures::CATEGORIE_2)); 
            } else {
                $produit->setMisEnAvant(true);
                $produit->setDateFinPromo(new DateTime("2030-01-" . $i));
                $produit->setEstEvaluable(true);
                $produit->setTauxTva($this->getReference(MDWTauxTvaFixtures::TAUX_TVA_2));
                $produit->addCategory($this->getReference(MDWCategoriesFixtures::CATEGORIE_1));
                $produit->addCategory($this->getReference(MDWCategoriesFixtures::CATEGORIE_3));
                $produit->addProduitsSuggere($this->getReference(MDWProduitsFixtures::PRODUIT_1));
                $produit->addProduitsSuggere($this->getReference(MDWProduitsFixtures::PRODUIT_2));
                $produit->addProduitsSuggere($this->getReference(MDWProduitsFixtures::PRODUIT_3));
                $produit->addProduitsSuggere($this->getReference(MDWProduitsFixtures::PRODUIT_4));
                $produit->addProduitsSuggere($this->getReference(MDWProduitsFixtures::PRODUIT_5));
            }

            $manager->persist($produit);
            $manager->flush();

            switch($i) {
                case 0:
                    $this->addReference(self::PRODUIT_1, $produit);
                    break;

                case 1:
                    $this->addReference(self::PRODUIT_2, $produit);
                    break;

                case 2:
                    $this->addReference(self::PRODUIT_3, $produit);
                    break;

                case 3:
                    $this->addReference(self::PRODUIT_4, $produit);
                    break;

                case 4:
                    $this->addReference(self::PRODUIT_5, $produit);
                    break;

                case 5:
                    $this->addReference(self::PRODUIT_6, $produit);
                    break;

                case 6:
                    $this->addReference(self::PRODUIT_7, $produit);
                    break;

                case 7:
                    $this->addReference(self::PRODUIT_8, $produit);
                    break;

                case 8:
                    $this->addReference(self::PRODUIT_9, $produit);
                    break;

                case 9:
                    $this->addReference(self::PRODUIT_10, $produit);
                    break;
            }
        }
    }
}
