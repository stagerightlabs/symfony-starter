<?php

namespace App\Action;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use StageRightLabs\Actions\Action;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CreateUserAction extends Action
{
    /**
     * Properties.
     */
    public User $user;
    protected UserPasswordHasherInterface $hasher;
    protected EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $hasher,
    ) {
        $this->entityManager = $entityManager;
        $this->hasher = $hasher;
    }

    /**
     * Handle the action.
     *
     * @param Action|array $input
     *
     * @return self
     */
    public function handle($input = [])
    {
        $this->user = new User();
        $this->user->setEmail($input['email']);

        // encode the plain password
        $this->user->setPassword(
            $this->hasher->hashPassword(
                $this->user,
                $input['password']
            )
        );

        // Persist the new user
        $this->entityManager->persist($this->user);
        $this->entityManager->flush();

        // do anything else you need here, like send an email

        return $this->complete();
    }

    /**
     * The input keys required by this action.
     *
     * @return array
     */
    public function required()
    {
        return [
            'email',
            'password',
        ];
    }
}
