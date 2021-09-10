<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\MDWTypesCaracteristiques;

class MDWTypesCaracteristiquesFixtures extends Fixture
{
    public const TYPE_CARACTERISTIQUE_POIDS = "poids";
    public const TYPE_CARACTERISTIQUE_COULEUR = "couleur";

    public function load(ObjectManager $manager)
    {
        $type_caracteristique_poids = new MDWTypesCaracteristiques();
        $type_caracteristique_poids->setNom("poids");
        $manager->persist($type_caracteristique_poids);

        $type_caracteristique_couleur = new MDWTypesCaracteristiques();
        $type_caracteristique_couleur->setNom("couleur");
        $manager->persist($type_caracteristique_couleur);
    
        $manager->flush();
        $this->addReference(self::TYPE_CARACTERISTIQUE_POIDS, $type_caracteristique_poids);
        $this->addReference(self::TYPE_CARACTERISTIQUE_COULEUR, $type_caracteristique_couleur);
    }
}
