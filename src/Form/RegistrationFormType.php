<?php

namespace App\Form;

use App\Entity\MDWUsers;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use App\Form\EventListener\ReCaptchaValidationListener;
use ReCaptcha\ReCaptcha;
use Symfony\Component\Form\Extension\Core\Type\CountryType;


class RegistrationFormType extends AbstractType
{
    private $reCaptcha;

    public function __construct(ReCaptcha $reCaptcha)
    {
        $this->reCaptcha = $reCaptcha;
    }

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
                'second_options' => ['label' => 'Confirmation de l\'adresse Email* :'],
                'invalid_message' => 'Les adresses e-mail entrées sont différentes',
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'first_options'  => ['label' => 'Mot de passe*:'],
                'second_options' => ['label' => 'Confirmation du mot de passe*:'],
                'invalid_message' => 'Les mots de passe entrés sont différents',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un mot de passe',
                    ]),
                    new Length([
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                        'maxMessage' => 'Votre mot de passe doit comporter moins de {{ limit }} caracteres'
                    ]),
                    new Regex([
                        'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%^&*_=+-]).{6,}$/',
                        'message' => 'Votre mot de passe doit contenir au moins 6 caracteres, 1 majuscule, 1 minuscule, 1 chiffre et un caractere special (! @ # $ % ^ & * _ = + - .)'
                    ])
                ],
            ])
            ->add('isVerified', CheckboxType::class, [
                'label' => 'J\'accepte les <a href="/commun/cgv">conditions d\'utilisation</a>',
                'label_html' => true,
            ])

            ->add('captcha', ReCaptchaType::class, [
                'mapped' => false,
                'type' => 'invisible' //invisible checkbox
            ])
        ;

        $builder->addEventSubscriber(new ReCaptchaValidationListener($this->reCaptcha));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MDWUsers::class,
        ]);
    }
}
