<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LuckyController extends AbstractController
{
    #[Route('/lucky', name: 'lucky')]
    public function index(LoggerInterface $logger): Response
    {
        $number = random_int(0, 100);

        return new Response(
            "<html><body>Lucky number: {$number}</body></html>"
        );

        // return $this->render('lucky/index.html.twig', [
        //     'controller_name' => 'LuckyController',
        // ]);
    }
}
