<?php

namespace App\Controller;

use App\Entity\JeuVideo;
use App\Form\JeuVideoType;
use App\Repository\JeuVideoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Contrôleur CRUD des jeux vidéo.
 *
 * Gère la liste, la consultation, l'ajout, la modification et la suppression
 * des jeux vidéo, ainsi que l'upload de leur image.
 */
#[Route('/jeu_video')]
final class JeuVideoController extends AbstractController
{
    /**
     * Sous-dossier de public/ où sont stockées les images des jeux.
     */
    private const UPLOAD_SUBDIR = 'uploads/jeux';

    /**
     * @param string           $publicDir Chemin absolu du dossier public/
     * @param SluggerInterface $slugger   Sert à générer des noms de fichiers propres
     */
    public function __construct(
        #[Autowire('%kernel.project_dir%/public')]
        private readonly string $publicDir,
        private readonly SluggerInterface $slugger,
    ) {
    }

    /**
     * READ — Liste de tous les jeux vidéo, avec les utilisateurs qui les ont dans leur collection (TP6).
     */
    #[Route(name: 'app_jeu_video_index', methods: ['GET'])]
    public function index(JeuVideoRepository $jeuVideoRepository): Response
    {
        return $this->render('jeu_video/index.html.twig', [
            'jeu_videos' => $jeuVideoRepository->findAllWithCollections(),
        ]);
    }

    /**
     * CREATE — Ajout d'un jeu vidéo (formulaire + enregistrement).
     */
    #[Route('/new', name: 'app_jeu_video_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $jeuVideo = new JeuVideo();
        $form = $this->createForm(JeuVideoType::class, $jeuVideo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->handleImageUpload($form, $jeuVideo)) {
                $jeuVideo->setCreatedAt(new \DateTimeImmutable());
                $jeuVideo->setUpdatedAt(null);

                $entityManager->persist($jeuVideo);
                $entityManager->flush();

                $this->addFlash('success', sprintf('Le jeu « %s » a été ajouté.', $jeuVideo->getTitre()));

                return $this->redirectToRoute('app_jeu_video_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('jeu_video/new.html.twig', [
            'jeu_video' => $jeuVideo,
            'form' => $form,
        ]);
    }

    /**
     * READ — Détail d'un jeu vidéo.
     */
    #[Route('/{id}', name: 'app_jeu_video_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(JeuVideo $jeuVideo): Response
    {
        return $this->render('jeu_video/show.html.twig', [
            'jeu_video' => $jeuVideo,
        ]);
    }

    /**
     * UPDATE — Modification d'un jeu vidéo.
     * La date de création n'est pas modifiée, la date de modification est mise à jour.
     */
    #[Route('/{id}/edit', name: 'app_jeu_video_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, JeuVideo $jeuVideo, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(JeuVideoType::class, $jeuVideo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->handleImageUpload($form, $jeuVideo)) {
                $jeuVideo->setUpdatedAt(new \DateTimeImmutable());
                $entityManager->flush();

                $this->addFlash('success', sprintf('Le jeu « %s » a été modifié.', $jeuVideo->getTitre()));

                return $this->redirectToRoute('app_jeu_video_show', ['id' => $jeuVideo->getId()], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('jeu_video/edit.html.twig', [
            'jeu_video' => $jeuVideo,
            'form' => $form,
        ]);
    }

    /**
     * DELETE — Suppression d'un jeu vidéo (et de son image).
     */
    #[Route('/{id}', name: 'app_jeu_video_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, JeuVideo $jeuVideo, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$jeuVideo->getId(), $request->getPayload()->getString('_token'))) {
            $titre = $jeuVideo->getTitre();
            $this->removeImage($jeuVideo->getImageUrl());

            $entityManager->remove($jeuVideo);
            $entityManager->flush();

            $this->addFlash('success', sprintf('Le jeu « %s » a été supprimé.', $titre));
        }

        return $this->redirectToRoute('app_jeu_video_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * Traite l'image envoyée via le champ non mappé « image » du formulaire :
     * renommage unique, déplacement dans public/uploads/jeux, enregistrement du chemin dans l'entité.
     *
     * @return bool false si l'upload a échoué (une erreur est alors ajoutée au formulaire)
     */
    private function handleImageUpload(FormInterface $form, JeuVideo $jeuVideo): bool
    {
        /** @var UploadedFile|null $imageFile */
        $imageFile = $form->get('image')->getData();

        if (null === $imageFile) {
            return true; // pas de nouvelle image : on garde l'existante
        }

        $originalName = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeName = $this->slugger->slug($jeuVideo->getTitre() ?: $originalName)->lower();
        $newFilename = sprintf('%s-%s.%s', $safeName, uniqid(), $imageFile->guessExtension() ?? 'bin');

        try {
            $imageFile->move($this->publicDir.'/'.self::UPLOAD_SUBDIR, $newFilename);
        } catch (FileException $e) {
            $form->get('image')->addError(new \Symfony\Component\Form\FormError(
                "L'image n'a pas pu être enregistrée. Veuillez réessayer."
            ));

            return false;
        }

        // On supprime l'ancienne image si elle existe
        $this->removeImage($jeuVideo->getImageUrl());
        $jeuVideo->setImageUrl(self::UPLOAD_SUBDIR.'/'.$newFilename);

        return true;
    }

    /**
     * Supprime physiquement une image du dossier public/ (si elle existe).
     */
    private function removeImage(?string $imageUrl): void
    {
        if (!$imageUrl || !str_starts_with($imageUrl, self::UPLOAD_SUBDIR.'/')) {
            return;
        }

        $path = $this->publicDir.'/'.$imageUrl;
        if (is_file($path)) {
            @unlink($path);
        }
    }
}
