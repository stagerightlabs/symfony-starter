<?php

namespace App\Tests\Actions;

use App\Entity\User;
use App\Tests\KernelTestCase;
use App\Action\CreateUserAction;
use App\Repository\UserRepository;

class CreateUserActionTest extends KernelTestCase
{
    /** @test */
    public function it_creates_a_user()
    {
        $action = $this->resolve(CreateUserAction::class);
        $userRepository = $this->resolve(UserRepository::class);
        $action = $action->run([
            'email' => 'test@test.com',
            'password' => 'password',
        ]);
        $fetched = $userRepository->findOneByEmail('test@test.com');

        $this->assertInstanceOf(User::class, $action->user);
        $this->assertInstanceOf(User::class, $fetched);
        $this->assertTrue($action->completed());
    }

    /** @test */
    public function it_does_not_create_duplicate_users()
    {
        $this->factory(User::class)->create([
            'email' => 'test@test.com',
        ]);
        $action = $this->resolve(CreateUserAction::class);
        $userRepository = $this->resolve(UserRepository::class);
        $action = $action->run([
            'email' => 'test@test.com',
            'password' => 'password',
        ]);
        $fetched = $userRepository->findBy(['email' => 'test@test.com']);

        $this->assertFalse($action->completed());
        $this->assertEquals(1, count($fetched));
    }
}
