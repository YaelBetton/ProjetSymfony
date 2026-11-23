<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/lucky')]

class LuckyController extends AbstractController
{
    #[Route('/number', 'lucky_number', methods: ['GET', 'POST'])]

    public function number(): Response
    {
        $number = random_int(0, 100);
        return new Response(
            '<html><body>Lucky number: ' . $number . '</body></html>'
        );

        return $this->render('lucky/number.html.twig', [
            'number' => $number,
        ]);
    }
}
