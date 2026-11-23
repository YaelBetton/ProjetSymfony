<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Exercice du TP1 : affiche un nombre aléatoire (rendu via le gabarit commun).
 */
#[Route('/lucky')]
class LuckyController extends AbstractController
{
    #[Route('/number', 'lucky_number', methods: ['GET'])]
    public function number(): Response
    {
        return $this->render('lucky/number.html.twig', [
            'number' => random_int(0, 100),
        ]);
    }
}
