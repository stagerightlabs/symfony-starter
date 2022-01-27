<?php

namespace App\Tests\Factory;

use App\Entity\User;

class UserFactory extends EntityFactory
{
    public function getDefinition(): array
    {
        return [
            'email' => $this->faker->safeEmail(),
            'password' => '$2y$13$go/oPgFNCEHAAnBrSzj.leF95ZFAps6OicFqcYXu0fAnis.KvCS/m', // 'password'
        ];
    }

    public function getClass(): string
    {
        return User::class;
    }
}
