<?php

namespace App\Form;

use App\Entity\MDWAvis;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AvisType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            /*->add('note')
            ->add('commentaire')
            ->add('produit')
            ->add('user')*/

            ->add('note', ChoiceType::class, [
                'label' => 'Note :',
                'choices' => [
                    '0/5' => 0,
                    '1/5' => 1,
                    '2/5' => 2,
                    '3/5' => 3,
                    '4/5' => 4,
                    '5/5' => 5,
                ],
                'choice_attr' => [
                    '5/5' => ['selected' => true],
                ],
                'attr' => [
                    'class' => 'champ-avis',
                ]
            ])
            ->add('commentaire', TextareaType::class, [
                'label' => 'Avis :',
                'attr' => [
                    'class' => 'champ-avis',
                    'rows' => '3',
                    'style' => 'resize:none',
                    'maxlength' => 255,
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MDWAvis::class,
        ]);
    }
}
