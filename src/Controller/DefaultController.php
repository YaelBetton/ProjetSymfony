<?php

namespace App\Controller;

use App\Repository\EditeurRepository;
use App\Repository\GenreRepository;
use App\Repository\JeuVideoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Page d'accueil de l'application.
 */
class DefaultController extends AbstractController
{
    /**
     * Accueil : présentation et accès rapide aux trois sections (avec leur nombre d'éléments).
     */
    #[Route('/', 'index', methods: ['GET'])]
    public function index(
        JeuVideoRepository $jeuVideoRepository,
        GenreRepository $genreRepository,
        EditeurRepository $editeurRepository,
    ): Response {
        return $this->render('default/index.html.twig', [
            'nb_jeux' => $jeuVideoRepository->count(),
            'nb_genres' => $genreRepository->count(),
            'nb_editeurs' => $editeurRepository->count(),
        ]);
    }
}
