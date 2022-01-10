<?php

namespace App\Controller;

use App\Action\CreateUserAction;
use App\Entity\User;
use App\Form\RegistrationFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('/{_locale}/register', name: 'register')]
    public function register(Request $request, CreateUserAction $action): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $action = $action->run([
                'email' => $form->get('email')->getData(),
                'password' => $form->get('plainPassword')->getData(),
            ]);

            if ($action->failed()) {
                $this->addFlash('error', $action->getMessage());

                return $this->redirectToRoute('register');
            }

            return $this->redirectToRoute('lucky');
        }

        return $this->render('auth/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
