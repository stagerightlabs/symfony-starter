<?php

namespace App\Controller;

use App\Action\LuckyNumberAction;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LuckyController extends AbstractController
{
    #[Route('/{_locale}/lucky', name: 'lucky')]
    public function index(LoggerInterface $logger): Response
    {
        // Simulating some complex business logic...
        $action = LuckyNumberAction::execute();

        // If the action fails we will throw an exception.
        // We could also redirect to a different page with a flash message.
        if ($action->failed()) {
            $logger->error($action->getMessage());
            throw new Exception($action->getMessage());
        }

        return $this->render('lucky/index.html.twig', [
            'number' => $action->lucky,
        ]);
    }
}
