<?php

namespace App\Form;

use App\Entity\Editeur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Url;

/**
 * Formulaire d'ajout / de modification d'un éditeur de jeux vidéo.
 * Les dates de création et de modification sont gérées par le contrôleur (pas dans le formulaire).
 */
class EditeurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'empty_data' => '',
                'constraints' => [
                    new NotBlank(message: "Le nom de l'éditeur est obligatoire."),
                    new Length(max: 150, maxMessage: 'Le nom ne doit pas dépasser {{ limit }} caractères.'),
                ],
            ])
            ->add('pays', TextType::class, [
                'label' => 'Pays',
                'required' => false,
                'constraints' => [
                    new Length(max: 100, maxMessage: 'Le pays ne doit pas dépasser {{ limit }} caractères.'),
                ],
            ])
            ->add('siteWeb', UrlType::class, [
                'label' => 'Site internet',
                'required' => false,
                'default_protocol' => 'https', // « www.exemple.com » devient « https://www.exemple.com »
                'attr' => ['placeholder' => 'https://www.exemple.com'],
                'constraints' => [
                    new Url(message: 'Veuillez saisir une adresse internet valide.', requireTld: true),
                    new Length(max: 255),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['rows' => 5],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Editeur::class,
        ]);
    }
}
