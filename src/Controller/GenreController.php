<?php

namespace App\Controller;

use App\Entity\Genre;
use App\Form\GenreType;
use App\Repository\GenreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur CRUD des genres de jeux vidéo.
 *
 * Gère la liste, la consultation, l'ajout, la modification et la suppression des genres.
 */
#[Route('/genre')]
final class GenreController extends AbstractController
{
    /**
     * READ — Liste de tous les genres (triés par nom).
     */
    #[Route(name: 'app_genre_index', methods: ['GET'])]
    public function index(GenreRepository $genreRepository): Response
    {
        return $this->render('genre/index.html.twig', [
            'genres' => $genreRepository->findBy([], ['nom' => 'ASC']),
        ]);
    }

    /**
     * CREATE — Ajout d'un genre.
     * La date de création est positionnée à maintenant, la date de modification reste vide.
     */
    #[Route('/new', name: 'app_genre_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $genre = new Genre();
        $form = $this->createForm(GenreType::class, $genre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $genre->setCreatedAt(new \DateTimeImmutable());
            $genre->setUpdatedAt(null);

            $entityManager->persist($genre);
            $entityManager->flush();

            $this->addFlash('success', sprintf('Le genre « %s » a été ajouté.', $genre->getNom()));

            return $this->redirectToRoute('app_genre_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('genre/new.html.twig', [
            'genre' => $genre,
            'form' => $form,
        ]);
    }

    /**
     * READ — Détail d'un genre.
     */
    #[Route('/{id}', name: 'app_genre_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Genre $genre): Response
    {
        return $this->render('genre/show.html.twig', [
            'genre' => $genre,
        ]);
    }

    /**
     * UPDATE — Modification d'un genre.
     * La date de création n'est pas modifiée, la date de modification est mise à jour.
     */
    #[Route('/{id}/edit', name: 'app_genre_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Genre $genre, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(GenreType::class, $genre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $genre->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            $this->addFlash('success', sprintf('Le genre « %s » a été modifié.', $genre->getNom()));

            return $this->redirectToRoute('app_genre_show', ['id' => $genre->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('genre/edit.html.twig', [
            'genre' => $genre,
            'form' => $form,
        ]);
    }

    /**
     * DELETE — Suppression d'un genre.
     * Refusée si des jeux vidéo sont encore rattachés à ce genre (la clé étrangère l'interdit).
     */
    #[Route('/{id}', name: 'app_genre_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, Genre $genre, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('delete'.$genre->getId(), $request->getPayload()->getString('_token'))) {
            $this->addFlash('error', 'La suppression a échoué (jeton de sécurité invalide). Veuillez réessayer.');

            return $this->redirectToRoute('app_genre_show', ['id' => $genre->getId()], Response::HTTP_SEE_OTHER);
        }

        $nbJeux = $genre->getJeuVideos()->count();
        if ($nbJeux > 0) {
            $this->addFlash('error', sprintf(
                'Impossible de supprimer le genre « %s » : %d jeu(x) vidéo y est/sont encore rattaché(s). Vous pouvez plutôt le désactiver.',
                $genre->getNom(),
                $nbJeux,
            ));

            return $this->redirectToRoute('app_genre_show', ['id' => $genre->getId()], Response::HTTP_SEE_OTHER);
        }

        $nom = $genre->getNom();
        $entityManager->remove($genre);
        $entityManager->flush();

        $this->addFlash('success', sprintf('Le genre « %s » a été supprimé.', $nom));

        return $this->redirectToRoute('app_genre_index', [], Response::HTTP_SEE_OTHER);
    }
}
