<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\MDWCategories;

class MDWCategoriesFixtures extends Fixture
{
    public const CATEGORIE_1 = "categorie 1";
    public const CATEGORIE_2 = "categorie 2";
    public const CATEGORIE_3 = "sous categorie 1";

    public function load(ObjectManager $manager)
    {
        $categorie_1 = new MDWCategories();
        $categorie_1->setNom("categorie 1");
        $categorie_1->setDescription("la categorie principale 1");
        $categorie_1->setImage("jv.jpg");
        $manager->persist($categorie_1);

        $categorie_2 = new MDWCategories();
        $categorie_2->setNom("categorie 2");
        $categorie_2->setDescription("la categorie principale 2");
        $categorie_2->setImage("jv.jpg");
        $manager->persist($categorie_2);

        $sous_categorie_1 = new MDWCategories();
        $sous_categorie_1->setNom("sous categorie 1");
        $sous_categorie_1->setDescription("la sous categorie 1");
        $sous_categorie_1->setImage("jv.jpg");
        $sous_categorie_1->addCategoriesParente($categorie_1);
        $manager->persist($sous_categorie_1);
    
        $manager->flush();
        $this->addReference(self::CATEGORIE_1, $categorie_1);
        $this->addReference(self::CATEGORIE_2, $categorie_2);
        $this->addReference(self::CATEGORIE_3, $sous_categorie_1);
    }
}
