<?php

namespace App\Controller;

use App\Entity\Editeur;
use App\Form\EditeurType;
use App\Repository\EditeurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur CRUD des éditeurs de jeux vidéo.
 *
 * Gère la liste, la consultation, l'ajout, la modification et la suppression des éditeurs.
 */
#[Route('/editeur')]
final class EditeurController extends AbstractController
{
    /**
     * READ — Liste de tous les éditeurs (triés par nom).
     */
    #[Route(name: 'app_editeur_index', methods: ['GET'])]
    public function index(EditeurRepository $editeurRepository): Response
    {
        return $this->render('editeur/index.html.twig', [
            'editeurs' => $editeurRepository->findBy([], ['nom' => 'ASC']),
        ]);
    }

    /**
     * CREATE — Ajout d'un éditeur.
     * La date de création est positionnée à maintenant, la date de modification reste vide.
     */
    #[Route('/new', name: 'app_editeur_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $editeur = new Editeur();
        $form = $this->createForm(EditeurType::class, $editeur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $editeur->setCreatedAt(new \DateTimeImmutable());
            $editeur->setUpdatedAt(null);

            $entityManager->persist($editeur);
            $entityManager->flush();

            $this->addFlash('success', sprintf("L'éditeur « %s » a été ajouté.", $editeur->getNom()));

            return $this->redirectToRoute('app_editeur_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('editeur/new.html.twig', [
            'editeur' => $editeur,
            'form' => $form,
        ]);
    }

    /**
     * READ — Détail d'un éditeur.
     */
    #[Route('/{id}', name: 'app_editeur_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Editeur $editeur): Response
    {
        return $this->render('editeur/show.html.twig', [
            'editeur' => $editeur,
        ]);
    }

    /**
     * UPDATE — Modification d'un éditeur.
     * La date de création n'est pas modifiée, la date de modification est mise à jour.
     */
    #[Route('/{id}/edit', name: 'app_editeur_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Editeur $editeur, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EditeurType::class, $editeur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $editeur->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            $this->addFlash('success', sprintf("L'éditeur « %s » a été modifié.", $editeur->getNom()));

            return $this->redirectToRoute('app_editeur_show', ['id' => $editeur->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('editeur/edit.html.twig', [
            'editeur' => $editeur,
            'form' => $form,
        ]);
    }

    /**
     * DELETE — Suppression d'un éditeur.
     * Refusée si des jeux vidéo sont encore rattachés à cet éditeur (la clé étrangère l'interdit).
     */
    #[Route('/{id}', name: 'app_editeur_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, Editeur $editeur, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('delete'.$editeur->getId(), $request->getPayload()->getString('_token'))) {
            $this->addFlash('error', 'La suppression a échoué (jeton de sécurité invalide). Veuillez réessayer.');

            return $this->redirectToRoute('app_editeur_show', ['id' => $editeur->getId()], Response::HTTP_SEE_OTHER);
        }

        $nbJeux = $editeur->getJeuVideos()->count();
        if ($nbJeux > 0) {
            $this->addFlash('error', sprintf(
                "Impossible de supprimer l'éditeur « %s » : %d jeu(x) vidéo y est/sont encore rattaché(s).",
                $editeur->getNom(),
                $nbJeux,
            ));

            return $this->redirectToRoute('app_editeur_show', ['id' => $editeur->getId()], Response::HTTP_SEE_OTHER);
        }

        $nom = $editeur->getNom();
        $entityManager->remove($editeur);
        $entityManager->flush();

        $this->addFlash('success', sprintf("L'éditeur « %s » a été supprimé.", $nom));

        return $this->redirectToRoute('app_editeur_index', [], Response::HTTP_SEE_OTHER);
    }
}
