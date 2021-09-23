<?php

namespace App\Form;

use App\Entity\MDWUsers;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CountryType;

class EditeCompteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom* :'
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom* :'
            ])
            ->add('adresse', TextareaType::class, [
                'label' => 'Adresse* :',
                'attr' => [
                    'maxlength' => 255,
                    'class' => 'no-resize',
                    'rows' => '5'
                ]
            ])
            ->add('ville', TextType::class, [
                'label' => 'Ville* :'
            ])
            ->add('code_postal', TextType::class, [
                'label' => 'Code postal* :'
            ])
            ->add("Pays",  CountryType::class, [
                "label" => "Pays* :",
                "attr" => [
                    "class" => "champ"
                ],
                "preferred_choices" => array('FR')
            ])
            ->add('telephone', TextType::class, [
                'label' => 'Téléphone* :'
            ])
            ->add('email', RepeatedType::class, [
                'type' => EmailType::class,
                'first_options'  => ['label' => 'Email* :'],
                'second_options' => [
                    'label' => 'Confirmation de l\'adresse Email* :', 
                    'attr' => [
                        'value' => '',
                    ]
                ],
                'invalid_message' => 'Les adresses e-mail entrées sont différentes',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MDWUsers::class,
        ]);
    }
}
