<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DefaultController extends AbstractController
{
    #[Route('/', 'index', methods: ['GET', 'POST'])]

    public function index()
    {
        return $this->render('default/index.html.twig');
    }
}
