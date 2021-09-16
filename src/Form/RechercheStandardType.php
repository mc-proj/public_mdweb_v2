<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
/*use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;*/
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;


class RechercheStandardType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('recherche', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez renseigner ce que vous recherchez'
                    ]),
                    new Length([
                        'min' => 3,
                        'minMessage' => 'Votre recherche doit comporter au moins {{ limit }} caractères',
                        'max' => 40,
                        'maxMessage' => 'Votre recherche doit comporter {{ limit }} caractères maximum',
                    ])
                ],
                'label' => ' ',
                'attr' => [
                    'placeholder' => 'Rechercher...'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
