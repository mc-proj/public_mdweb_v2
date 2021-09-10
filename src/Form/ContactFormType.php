<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ContactFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez renseigner votre nom'
                    ]),
                    new Length([
                        'min' => 3,
                        'minMessage' => 'Votre nom doit comporter au moins {{ limit }} caractères',
                        'max' => 40,
                        'maxMessage' => 'Votre nom doit comporter {{ limit }} caractères maximum',
                    ])
                ],
                'label' => 'Nom* :'
            ])
            ->add('prenom', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez renseigner votre prénom'
                    ]),
                    new Length([
                        'min' => 3,
                        'minMessage' => 'Votre prénom doit comporter au moins {{ limit }} caractères',
                        'max' => 40,
                        'maxMessage' => 'Votre prénom doit comporter {{ limit }} caractères maximum',
                    ])
                ],
                'label' => 'Prénom* :'
            ])
            ->add('email', EmailType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez renseigner votre adresse email afin que nous puissions vous recontacter'
                    ]),
                    new Length([
                        'min' => 5,
                        'minMessage' => 'Votre adresse email doit comporter au moins {{ limit }} caractères',
                        'max' => 255,
                        'maxMessage' => 'Votre adresse email doit comporter {{ limit }} caractères maximum',
                    ])
                ],
                'label' => 'Adresse email* :'
            ])
            ->add('message', TextareaType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez rédiger un message'
                    ]),
                    new Length([
                        'min' => 5,
                        'minMessage' => 'Votre message doit comporter au moins {{ limit }} caractères',
                        'max' => 255,
                        'maxMessage' => 'Votre message doit comporter {{ limit }} caractères maximum',
                    ])
                ],
                'label' => 'Message* :'
            ])
        ;
    }
}
