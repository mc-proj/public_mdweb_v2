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
                    "class" => "champ",
                    "placeholder" => "Nom",
                ],
            ])
            ->add("prenom", TextType::class, [
                "label" => " ",
                "attr" => [
                    "class" => "champ",
                    "placeholder" => "Prenom"
                ],
            ])
            ->add("adresse", TextareaType::class, [
                "label" => " ",
                "attr" => [
                    "class" => "champ",
                    "placeholder" => "Adresse",
                    "maxlength" => "255",
                    "rows" => "3",
                    "style" => "resize:none"
                ],
            ])
            ->add("ville", TextType::class, [
                "label" => " ",
                "attr" => [
                    "class" => "champ",
                    "placeholder" => "Ville"
                ],
            ])
            ->add("code_postal", TextType::class, [
                "label" => " ",
                "attr" => [
                    "class" => "champ",
                    "placeholder" => "Code postal"
                ],
            ])
            ->add("Pays",  CountryType::class, [
                "label" => " ",
                "attr" => [
                    "class" => "champ"
                ],
                "preferred_choices" => array('FR')
            ])
            ->add("telephone", TextType::class, [
                "label" => " ",
                "attr" => [
                    "class" => "champ",
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
