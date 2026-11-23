<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Enum\StatutJeuEnum;
use App\Form\UtilisateurType;
use App\Repository\CollectRepository;
use App\Repository\UtilisateurRepository;
use App\Service\ImageUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur des utilisateurs et de leurs collections de jeux vidéo (TP6).
 *
 *  - index : liste des collections des utilisateurs ;
 *  - show  : profil d'un utilisateur et détail de sa collection ;
 *  - new / edit / delete : gestion des utilisateurs.
 *
 * Les jeux de la collection eux-mêmes sont gérés par CollectController.
 */
#[Route('/utilisateur')]
final class UtilisateurController extends AbstractController
{
    /** Sous-dossier de public/ où sont stockées les images de profil */
    private const UPLOAD_SUBDIR = 'uploads/utilisateurs';

    public function __construct(
        private readonly ImageUploader $imageUploader,
    ) {
    }

    /**
     * READ — Liste des collections des utilisateurs.
     */
    #[Route(name: 'app_utilisateur_index', methods: ['GET'])]
    public function index(UtilisateurRepository $utilisateurRepository): Response
    {
        return $this->render('utilisateur/index.html.twig', [
            'utilisateurs' => $utilisateurRepository->findAllWithCollection(),
        ]);
    }

    /**
     * CREATE — Ajout d'un utilisateur (sa collection est vide au départ).
     */
    #[Route('/new', name: 'app_utilisateur_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $utilisateur = new Utilisateur();
        $form = $this->createForm(UtilisateurType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $this->handleImageUpload($form, $utilisateur)) {
            $utilisateur->setCreatedAt(new \DateTimeImmutable());
            $utilisateur->setUpdatedAt(null);

            $entityManager->persist($utilisateur);
            $entityManager->flush();

            $this->addFlash('success', sprintf('L\'utilisateur « %s » a été ajouté.', $utilisateur->getPseudonyme()));

            return $this->redirectToRoute('app_utilisateur_show', ['id' => $utilisateur->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('utilisateur/new.html.twig', [
            'utilisateur' => $utilisateur,
            'form' => $form,
        ]);
    }

    /**
     * READ — Profil d'un utilisateur et détail de sa collection (avec le nombre de jeux par statut).
     */
    #[Route('/{id}', name: 'app_utilisateur_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Utilisateur $utilisateur, CollectRepository $collectRepository): Response
    {
        $collects = $collectRepository->findByUtilisateur($utilisateur);

        // Nombre de jeux par statut, dans l'ordre de l'énumération (statuts absents ignorés)
        $parStatut = [];
        foreach (StatutJeuEnum::cases() as $statut) {
            $nb = \count(array_filter($collects, fn ($c) => $c->getStatut() === $statut));
            if ($nb > 0) {
                $parStatut[] = ['statut' => $statut, 'nb' => $nb];
            }
        }

        return $this->render('utilisateur/show.html.twig', [
            'utilisateur' => $utilisateur,
            'collects' => $collects,
            'par_statut' => $parStatut,
        ]);
    }

    /**
     * UPDATE — Modification d'un utilisateur.
     * La date de création n'est pas modifiée, la date de modification est mise à jour.
     */
    #[Route('/{id}/edit', name: 'app_utilisateur_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Utilisateur $utilisateur, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UtilisateurType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $this->handleImageUpload($form, $utilisateur)) {
            $utilisateur->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            $this->addFlash('success', sprintf('L\'utilisateur « %s » a été modifié.', $utilisateur->getPseudonyme()));

            return $this->redirectToRoute('app_utilisateur_show', ['id' => $utilisateur->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('utilisateur/edit.html.twig', [
            'utilisateur' => $utilisateur,
            'form' => $form,
        ]);
    }

    /**
     * DELETE — Suppression d'un utilisateur, de sa collection et de son image de profil.
     */
    #[Route('/{id}', name: 'app_utilisateur_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, Utilisateur $utilisateur, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('delete'.$utilisateur->getId(), $request->getPayload()->getString('_token'))) {
            $this->addFlash('error', 'La suppression a échoué (jeton de sécurité invalide). Veuillez réessayer.');

            return $this->redirectToRoute('app_utilisateur_show', ['id' => $utilisateur->getId()], Response::HTTP_SEE_OTHER);
        }

        $pseudo = $utilisateur->getPseudonyme();
        $this->imageUploader->remove($utilisateur->getImageProfil(), self::UPLOAD_SUBDIR);

        $entityManager->remove($utilisateur);
        $entityManager->flush();

        $this->addFlash('success', sprintf('L\'utilisateur « %s » et sa collection ont été supprimés.', $pseudo));

        return $this->redirectToRoute('app_utilisateur_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * Traite l'image de profil envoyée via le champ non mappé « imageProfil ».
     *
     * @return bool false si l'upload a échoué (une erreur est alors ajoutée au formulaire)
     */
    private function handleImageUpload(FormInterface $form, Utilisateur $utilisateur): bool
    {
        /** @var UploadedFile|null $imageFile */
        $imageFile = $form->get('imageProfil')->getData();
        if (null === $imageFile) {
            return true; // pas de nouvelle image : on garde l'existante
        }

        try {
            $path = $this->imageUploader->upload($imageFile, self::UPLOAD_SUBDIR, (string) $utilisateur->getPseudonyme());
        } catch (FileException) {
            $form->get('imageProfil')->addError(new FormError('L\'image n\'a pas pu être enregistrée. Veuillez réessayer.'));

            return false;
        }

        $this->imageUploader->remove($utilisateur->getImageProfil(), self::UPLOAD_SUBDIR);
        $utilisateur->setImageProfil($path);

        return true;
    }
}
