<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase as SymfonyKernelTestCase;
use Symfony\Bundle\FrameworkBundle\Test\TestContainer;

class KernelTestCase extends SymfonyKernelTestCase
{
    use TestUtilities;

    protected TestContainer $container;

    public function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        $this->container = static::getContainer();
    }
}
