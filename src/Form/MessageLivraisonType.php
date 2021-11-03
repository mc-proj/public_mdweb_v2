<?php

namespace App\Form;

use App\Entity\MDWPaniers;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class MessageLivraisonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("message", TextareaType::class, [
                "label" => "Notes de commande (facultatif)",
                "attr" => [
                    "class" => "champ",
                    "maxlength" => "255",
                    "rows" => "3",
                    "placeholder" => "Commentaires concernant votre commande, ex. : consignes de livraison."
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MDWPaniers::class,
        ]);
    }
}
