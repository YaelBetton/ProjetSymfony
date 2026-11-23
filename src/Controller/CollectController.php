<?php

namespace App\Controller;

use App\Entity\Collect;
use App\Entity\Utilisateur;
use App\Form\CollectType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur des jeux de la collection d'un utilisateur (entité Collect, TP6) :
 * ajout d'un jeu dans une collection, consultation, modification (statut, prix...) et retrait.
 *
 * La liste des collections et la collection d'un utilisateur sont affichées par UtilisateurController.
 */
#[Route('/collection')]
final class CollectController extends AbstractController
{
    /**
     * CREATE — Ajout d'un jeu dans la collection de l'utilisateur {utilisateur}.
     */
    #[Route('/new/{utilisateur}', name: 'app_collect_new', requirements: ['utilisateur' => '\d+'], methods: ['GET', 'POST'])]
    public function new(Request $request, Utilisateur $utilisateur, EntityManagerInterface $entityManager): Response
    {
        $collect = new Collect();
        $collect->setUtilisateur($utilisateur);

        $form = $this->createForm(CollectType::class, $collect, ['utilisateur' => $utilisateur]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $now = new \DateTimeImmutable();
            $collect->setCreatedAt($now);
            $collect->setUpdatedAt(null);
            $collect->setDateModifStatut($now);

            $entityManager->persist($collect);
            $entityManager->flush();

            $this->addFlash('success', sprintf(
                'Le jeu « %s » a été ajouté à la collection de %s.',
                $collect->getJeuVideo()?->getTitre(),
                $utilisateur->getPseudonyme(),
            ));

            return $this->redirectToRoute('app_utilisateur_show', ['id' => $utilisateur->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('collect/new.html.twig', [
            'collect' => $collect,
            'utilisateur' => $utilisateur,
            'form' => $form,
        ]);
    }

    /**
     * READ — Détail d'un jeu de la collection d'un utilisateur.
     */
    #[Route('/{id}', name: 'app_collect_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Collect $collect): Response
    {
        return $this->render('collect/show.html.twig', [
            'collect' => $collect,
        ]);
    }

    /**
     * UPDATE — Modification d'un jeu de la collection (statut, prix, date d'achat, commentaire).
     * La date de création n'est pas modifiée, la date de modification est mise à jour ;
     * la date de changement de statut n'est mise à jour que si le statut change (cf. Collect::setStatut()).
     */
    #[Route('/{id}/edit', name: 'app_collect_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Collect $collect, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CollectType::class, $collect, [
            'utilisateur' => $collect->getUtilisateur(),
            'is_edit' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $collect->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            $this->addFlash('success', sprintf(
                'Le jeu « %s » de la collection de %s a été modifié.',
                $collect->getJeuVideo()?->getTitre(),
                $collect->getUtilisateur()?->getPseudonyme(),
            ));

            return $this->redirectToRoute('app_collect_show', ['id' => $collect->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('collect/edit.html.twig', [
            'collect' => $collect,
            'form' => $form,
        ]);
    }

    /**
     * DELETE — Retrait d'un jeu de la collection (le jeu lui-même n'est pas supprimé).
     */
    #[Route('/{id}', name: 'app_collect_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, Collect $collect, EntityManagerInterface $entityManager): Response
    {
        $utilisateur = $collect->getUtilisateur();

        if (!$this->isCsrfTokenValid('delete'.$collect->getId(), $request->getPayload()->getString('_token'))) {
            $this->addFlash('error', 'Le retrait a échoué (jeton de sécurité invalide). Veuillez réessayer.');

            return $this->redirectToRoute('app_collect_show', ['id' => $collect->getId()], Response::HTTP_SEE_OTHER);
        }

        $titre = $collect->getJeuVideo()?->getTitre();
        $utilisateur?->removeCollect($collect);
        $entityManager->remove($collect);
        $entityManager->flush();

        $this->addFlash('success', sprintf(
            'Le jeu « %s » a été retiré de la collection de %s.',
            $titre,
            $utilisateur?->getPseudonyme(),
        ));

        return $this->redirectToRoute('app_utilisateur_show', ['id' => $utilisateur?->getId()], Response::HTTP_SEE_OTHER);
    }
}
