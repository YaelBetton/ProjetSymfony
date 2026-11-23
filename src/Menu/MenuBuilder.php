<?php

namespace App\Menu;

use App\Entity\Editeur;
use App\Entity\Genre;
use App\Entity\JeuVideo;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Construit le menu principal de l'application (KnpMenuBundle).
 *
 * Ce même arbre sert à deux choses :
 *  - le menu de navigation (seul le 1er niveau est affiché, cf. base.html.twig) ;
 *  - le fil d'Ariane : on ajoute dynamiquement, sous la section courante, les items
 *    « Ajouter », « <nom de l'élément> » et « Modifier » selon la page affichée.
 *
 * L'item courant est détecté par le RouteVoter de KnpMenu grâce à l'option « route ».
 */
#[AutoconfigureTag('knp_menu.menu_builder', ['method' => 'createMainMenu', 'alias' => 'main'])]
final class MenuBuilder
{
    /**
     * Sections CRUD du menu.
     * Clé = préfixe des routes (app_<clé>_index, app_<clé>_new, ...).
     *
     * @var array<string, array{label: string, entity: class-string, newLabel: string}>
     */
    private const SECTIONS = [
        'jeu_video' => ['label' => 'Jeux vidéo', 'entity' => JeuVideo::class, 'newLabel' => 'Ajouter un jeu'],
        'genre'     => ['label' => 'Genres',     'entity' => Genre::class,    'newLabel' => 'Ajouter un genre'],
        'editeur'   => ['label' => 'Éditeurs',   'entity' => Editeur::class,  'newLabel' => 'Ajouter un éditeur'],
    ];

    public function __construct(
        private readonly FactoryInterface $factory,
        private readonly RequestStack $requestStack,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param array<string, mixed> $options
     */
    public function createMainMenu(array $options): ItemInterface
    {
        // La racine porte le libellé « Accueil » : c'est le 1er élément du fil d'Ariane.
        $menu = $this->factory->createItem('root', ['label' => 'Accueil', 'route' => 'index']);

        $menu->addChild('home', ['label' => 'Accueil', 'route' => 'index']);

        $request = $this->requestStack->getMainRequest();
        $currentRoute = (string) $request?->attributes->get('_route');
        $currentId = $request?->attributes->get('id');

        foreach (self::SECTIONS as $prefix => $section) {
            $sectionItem = $menu->addChild($prefix, [
                'label' => $section['label'],
                'route' => sprintf('app_%s_index', $prefix),
            ]);

            // Sous-items ajoutés uniquement pour la page courante (utilisés par le fil d'Ariane)
            if ($currentRoute === sprintf('app_%s_new', $prefix)) {
                $sectionItem->addChild('new', ['label' => $section['newLabel'], 'route' => $currentRoute]);
            }

            if (null !== $currentId && \in_array($currentRoute, [sprintf('app_%s_show', $prefix), sprintf('app_%s_edit', $prefix)], true)) {
                $entity = $this->entityManager->find($section['entity'], (int) $currentId);
                if (null === $entity) {
                    continue;
                }

                $showItem = $sectionItem->addChild('show', [
                    'label' => $this->entityLabel($entity),
                    'route' => sprintf('app_%s_show', $prefix),
                    'routeParameters' => ['id' => $currentId],
                ]);

                if ($currentRoute === sprintf('app_%s_edit', $prefix)) {
                    $showItem->addChild('edit', [
                        'label' => 'Modifier',
                        'route' => $currentRoute,
                        'routeParameters' => ['id' => $currentId],
                    ]);
                }
            }
        }

        return $menu;
    }

    /**
     * Libellé lisible d'une entité pour le fil d'Ariane (titre d'un jeu, nom d'un genre / éditeur).
     */
    private function entityLabel(object $entity): string
    {
        return match (true) {
            $entity instanceof JeuVideo => (string) $entity->getTitre(),
            $entity instanceof Genre, $entity instanceof Editeur => (string) $entity->getNom(),
            default => '',
        };
    }
}
