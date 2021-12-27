<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LuckyController extends AbstractController
{
    #[Route('/{_locale}/lucky', name: 'lucky')]
    public function index(LoggerInterface $logger): Response
    {
        return $this->render('lucky/index.html.twig', [
            'number' => random_int(0, 100),
        ]);
    }
}
