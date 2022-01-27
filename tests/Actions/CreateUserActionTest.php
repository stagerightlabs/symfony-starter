<?php

namespace App\Tests\Actions;

use App\Action\CreateUserAction;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\KernelTestCase;

class CreateUserActionTest extends KernelTestCase
{
    /** @test */
    public function itCreatesAUser()
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
    public function itDoesNotCreateDuplicateUsers()
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
