<?php

namespace App\Form;

use App\Entity\Collect;
use App\Entity\JeuVideo;
use App\Entity\Utilisateur;
use App\Enum\StatutJeuEnum;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

/**
 * Formulaire d'ajout / de modification d'un jeu dans la collection d'un utilisateur.
 *
 * Options :
 *  - utilisateur : propriétaire de la collection (les jeux qu'il possède déjà ne sont pas proposés) ;
 *  - is_edit     : en modification, le jeu n'est plus modifiable (on modifie le statut, le prix...).
 *
 * L'utilisateur, les dates de création / modification et la date de changement de statut
 * sont gérés par le contrôleur et l'entité (pas dans le formulaire).
 */
class CollectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Utilisateur|null $utilisateur */
        $utilisateur = $options['utilisateur'];

        $builder
            ->add('jeuVideo', EntityType::class, [
                'class' => JeuVideo::class,
                'label' => 'Jeu vidéo',
                'choice_label' => 'titre',
                'placeholder' => '-- Choisir un jeu --',
                'disabled' => $options['is_edit'],
                'help' => $options['is_edit'] ? null : 'Seuls les jeux absents de la collection sont proposés.',
                'constraints' => [new NotBlank(message: 'Veuillez choisir un jeu.')],
                'query_builder' => function (EntityRepository $er) use ($utilisateur, $options) {
                    $qb = $er->createQueryBuilder('j')->orderBy('j.titre', 'ASC');

                    // En ajout : on exclut les jeux déjà présents dans la collection de l'utilisateur
                    if (!$options['is_edit'] && null !== $utilisateur?->getId()) {
                        $qb->andWhere('j.id NOT IN (
                                SELECT IDENTITY(c.jeuVideo) FROM App\Entity\Collect c WHERE c.utilisateur = :utilisateur
                            )')
                            ->setParameter('utilisateur', $utilisateur);
                    }

                    return $qb;
                },
            ])
            ->add('statut', EnumType::class, [
                'class' => StatutJeuEnum::class,
                'label' => 'Statut',
                // Libellé lisible fourni par l'énumération (ex. EN_COURS → « En cours »)
                'choice_label' => fn (StatutJeuEnum $statut) => $statut->getLabel(),
                // Statut envoyé vide : POSSEDE par défaut (évite de passer null à setStatut())
                'empty_data' => StatutJeuEnum::POSSEDE->value,
                'help' => 'La date de changement de statut est mise à jour automatiquement.',
            ])
            ->add('prixAchat', NumberType::class, [
                'label' => 'Prix d\'achat',
                'input' => 'string',
                'scale' => 2,
                'html5' => true,
                'required' => false,
                'attr' => ['min' => 0, 'step' => '0.01'],
                'help' => 'Prix payé par l\'utilisateur, en €',
                'constraints' => [new PositiveOrZero(message: 'Le prix ne peut pas être négatif.')],
            ])
            ->add('dateAchat', DateType::class, [
                'label' => 'Date d\'achat',
                'widget' => 'single_text',
                'required' => false,
                'help' => 'Ne peut pas être antérieure à la date de sortie du jeu.',
                'constraints' => [
                    new LessThanOrEqual('today', message: 'La date d\'achat ne peut pas être dans le futur.'),
                ],
            ])
            ->add('commentaire', TextareaType::class, [
                'label' => 'Commentaire',
                'required' => false,
                'attr' => ['rows' => 4],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Collect::class,
            'utilisateur' => null,
            'is_edit' => false,
        ]);
        $resolver->setAllowedTypes('utilisateur', ['null', Utilisateur::class]);
        $resolver->setAllowedTypes('is_edit', 'bool');
    }
}
