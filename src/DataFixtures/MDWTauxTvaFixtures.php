<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\MDWTauxTva;

class MDWTauxTvaFixtures extends Fixture
{
    public const TAUX_TVA_1 = "taux tva 1";
    public const TAUX_TVA_2 = "taux tva 2";

    public function load(ObjectManager $manager)
    {
        $taux_tva_1 = new MDWTauxTva();
        $taux_tva_1->setIntitule("taux tva standard");
        $taux_tva_1->setTaux(1960);  //19.6%
        $manager->persist($taux_tva_1);

        $taux_tva_2 = new MDWTauxTva();
        $taux_tva_2->setIntitule("taux tva bas");
        $taux_tva_2->setTaux(550);  //5.5%
        $manager->persist($taux_tva_2);
    
        $manager->flush();
        $this->addReference(self::TAUX_TVA_1, $taux_tva_1);
        $this->addReference(self::TAUX_TVA_2, $taux_tva_2);
    }
}
