<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
//use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
//use Symfony\Component\Form\Extension\Core\Type\EmailType;

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
                "constraints" => [

                    new NotBlank([
                        "message" => "Veuillez renseigner ce champ"
                    ]),
                    new Length([
                        "min" => 5,
                        "max" => 255,
                    ])
                ]
            ])
            ->add("prenom", TextType::class, [
                "label" => " ",
                "attr" => [
                    "class" => "champ",
                    "placeholder" => "Prenom"
                ],
                "constraints" => [

                    new NotBlank([
                        "message" => "Veuillez renseigner ce champ"
                    ]),
                    new Length([
                        "min" => 3,
                        "max" => 255,
                    ])
                ]
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
                "constraints" => [

                    new NotBlank([
                        "message" => "Veuillez renseigner ce champ"
                    ]),
                    new Length([
                        "min" => 5,
                        "max" => 255,
                    ])
                ]
            ])
            ->add("ville", TextType::class, [
                "label" => " ",
                "attr" => [
                    "class" => "champ",
                    "placeholder" => "Ville"
                ],
                "constraints" => [

                    new NotBlank([
                        "message" => "Veuillez renseigner ce champ"
                    ]),
                    new Length([
                        "min" => 5,
                        "max" => 45,
                    ])
                ]
            ])
            ->add("code_postal", TextType::class, [
                "label" => " ",
                "attr" => [
                    "class" => "champ",
                    "placeholder" => "Code postal"
                ],
                "constraints" => [

                    new NotBlank([
                        "message" => "Veuillez renseigner ce champ"
                    ]),
                    new Length([
                        "min" => 4,
                        "max" => 255,
                    ])
                ]
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
                "constraints" => [
                    new NotBlank([
                        "message" => "Veuillez renseigner un numéro de téléphone"
                    ]),
                    new Length([
                        "min" => 5,
                        "max" => 45,
                    ])
                ],
            ])
            /*->add("email", EmailType::class, [
                "label" => " ",
                "attr" => [
                    "class" => "champ",
                    "placeholder" => "Adresse e-mail",
                ],
                "constraints" => [
                    new NotBlank([
                        "message" => "Veuillez renseigner une adresse mail",
                    ]),
                    new Length([
                        "min" => 6,
                        "max" => 255,
                    ]),
                    new Regex([
                        "pattern" => "/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/",
                        "message" => "Adresse email invalide"
                    ])
                ],
            ])*/
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
