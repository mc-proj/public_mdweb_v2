<?php

namespace App\Form;

use App\Entity\MDWAdressesLivraison;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class AdresseLivraisonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            //->add('field_name')
            ->add("nom", TextType::class, [
                "label" => " ",
                "attr" => [
                    "class" => "champ champ-adresse",
                    "placeholder" => "Nom",
                ],
            ])
            ->add("prenom", TextType::class, [
                "label" => " ",
                "attr" => [
                    "class" => "champ champ-adresse",
                    "placeholder" => "Prenom"
                ],
            ])
            ->add("adresse", TextareaType::class, [
                "label" => " ",
                "attr" => [
                    "class" => "champ champ-adresse",
                    "placeholder" => "Adresse",
                    "maxlength" => "255",
                    "rows" => "3",
                    "style" => "resize:none"
                ],
            ])
            ->add("ville", TextType::class, [
                "label" => " ",
                "attr" => [
                    "class" => "champ champ-adresse",
                    "placeholder" => "Ville"
                ],
            ])
            ->add("code_postal", TextType::class, [
                "label" => " ",
                "attr" => [
                    "class" => "champ champ-adresse",
                    "placeholder" => "Code postal"
                ],
            ])
            ->add("Pays",  CountryType::class, [
                "label" => " ",
                "attr" => [
                    "class" => "champ champ-adresse"
                ],
                "preferred_choices" => array('FR')
            ])
            ->add("telephone", TextType::class, [
                "label" => " ",
                "attr" => [
                    "class" => "champ champ-adresse",
                    "placeholder" => "Téléphone",
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
            'data_class' => MDWAdressesLivraison::class,
        ]);
    }
}
