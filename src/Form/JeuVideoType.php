<?php

namespace App\Form;

use App\Entity\Developpeur;
use App\Entity\Editeur;
use App\Entity\Genre;
use App\Entity\JeuVideo;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Formulaire d'ajout / de modification d'un jeu vidéo.
 * Les dates de création et de modification sont gérées par le contrôleur (pas dans le formulaire).
 */
class JeuVideoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre',
                'empty_data' => '', // évite une erreur 500 si le champ est envoyé vide
                'constraints' => [
                    new NotBlank(message: 'Le titre est obligatoire.'),
                    new Length(max: 255),
                ],
            ])
            ->add('genre', EntityType::class, [
                'class' => Genre::class,
                'label' => 'Genre',
                'choice_label' => 'nom',
                'placeholder' => '-- Choisir un genre --',
                'constraints' => [new NotBlank(message: 'Veuillez choisir un genre.')],
                // Seuls les genres actifs sont sélectionnables
                'query_builder' => fn (EntityRepository $er) => $er->createQueryBuilder('g')
                    ->where('g.actif = :actif')
                    ->setParameter('actif', true)
                    ->orderBy('g.nom', 'ASC'),
            ])
            ->add('editeur', EntityType::class, [
                'class' => Editeur::class,
                'label' => 'Éditeur',
                'choice_label' => 'nom',
                'placeholder' => '-- Choisir un éditeur --',
                'constraints' => [new NotBlank(message: 'Veuillez choisir un éditeur.')],
                'query_builder' => fn (EntityRepository $er) => $er->createQueryBuilder('e')
                    ->orderBy('e.nom', 'ASC'),
            ])
            ->add('developpeur', EntityType::class, [
                'class' => Developpeur::class,
                'label' => 'Développeur',
                'choice_label' => 'nom',
                'placeholder' => '-- Choisir un développeur --',
                'constraints' => [new NotBlank(message: 'Veuillez choisir un développeur.')],
                'query_builder' => fn (EntityRepository $er) => $er->createQueryBuilder('d')
                    ->orderBy('d.nom', 'ASC'),
            ])
            ->add('dateSortie', DateType::class, [
                'label' => 'Date de sortie',
                'widget' => 'single_text', // <input type="date"> : calendrier natif du navigateur
                'required' => false,
            ])
            ->add('prix', NumberType::class, [
                'label' => 'Prix',
                'input' => 'string',   // la propriété prix est un string (type decimal)
                'scale' => 2,
                'html5' => true,       // <input type="number"> : nombres uniquement
                'required' => false,
                'attr' => ['min' => 0, 'step' => '0.01'],
                'help' => 'Le prix est en €',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['rows' => 5],
            ])
            ->add('image', FileType::class, [
                'label' => 'Image',
                'mapped' => false,     // traité manuellement dans le contrôleur
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
            'data_class' => JeuVideo::class,
        ]);
    }
}
