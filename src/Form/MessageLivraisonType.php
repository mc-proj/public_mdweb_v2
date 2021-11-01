<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class MessageLivraisonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            //->add('field_name')
            ->add("message", TextareaType::class, [
                "label" => "Notes de commande (facultatif)",
                "attr" => [
                    "class" => "champ",
                    "maxlength" => "255",
                    "rows" => "3",
                    "placeholder" => "Commentaires concernant votre commande, ex. : consignes de livraison."
                ],
                "constraints" => [
                    new NotBlank([
                        "message" => "Votre message est facultatif mais ne doit pas etre vide"
                    ]),
                    new Length([
                        "min" => 3,
                        "max" => 255,
                    ])
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
