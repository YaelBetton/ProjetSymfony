<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Formulaire d'ajout / de modification d'un utilisateur.
 * Les dates de création et de modification sont gérées par le contrôleur (pas dans le formulaire).
 */
class UtilisateurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'empty_data' => '',
                'attr' => ['autocomplete' => 'given-name'],
                'constraints' => [
                    new NotBlank(message: 'Le prénom est obligatoire.'),
                    new Length(max: 100, maxMessage: 'Le prénom ne doit pas dépasser {{ limit }} caractères.'),
                ],
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'empty_data' => '',
                'help' => 'Le nom sera enregistré en majuscules.',
                'attr' => ['autocomplete' => 'family-name'],
                'constraints' => [
                    new NotBlank(message: 'Le nom est obligatoire.'),
                    new Length(max: 100, maxMessage: 'Le nom ne doit pas dépasser {{ limit }} caractères.'),
                ],
            ])
            ->add('pseudonyme', TextType::class, [
                'label' => 'Pseudo',
                'empty_data' => '',
                'help' => '30 caractères maximum.',
                'attr' => ['maxlength' => 30, 'autocomplete' => 'nickname'],
                'constraints' => [
                    new NotBlank(message: 'Le pseudo est obligatoire.'),
                    new Length(max: 30, maxMessage: 'Le pseudo ne doit pas dépasser {{ limit }} caractères.'),
                ],
            ])
            ->add('mail', EmailType::class, [
                'label' => 'Adresse mail',
                'empty_data' => '',
                'help' => 'Exemple : prenom.nom@exemple.fr',
                'attr' => ['autocomplete' => 'email'],
                'constraints' => [
                    new NotBlank(message: 'L\'adresse mail est obligatoire.'),
                    new Email(message: 'L\'adresse mail {{ value }} n\'est pas valide.'),
                    new Length(max: 180),
                ],
            ])
            ->add('dateNaissance', DateType::class, [
                'label' => 'Date de naissance',
                'widget' => 'single_text',
                'attr' => ['autocomplete' => 'bday'],
                'constraints' => [
                    new NotBlank(message: 'La date de naissance est obligatoire.'),
                    new LessThan('today', message: 'La date de naissance doit être dans le passé.'),
                ],
            ])
            ->add('imageProfil', FileType::class, [
                'label' => 'Image de profil',
                'mapped' => false,     // traitée manuellement dans le contrôleur (ImageUploader)
                'required' => false,
                'help' => 'Formats acceptés : JPG, PNG, GIF, WEBP — 5 Mo maximum',
                'attr' => ['accept' => 'image/*'],
                'constraints' => [
                    new File(
                        maxSize: '5M',
                        mimeTypes: ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
                        maxSizeMessage: 'L\'image ne doit pas dépasser 5 Mo.',
                        mimeTypesMessage: 'Veuillez envoyer une image valide (JPG, PNG, GIF ou WEBP).',
                    ),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}
