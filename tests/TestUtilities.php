<?php

namespace App\Tests;

use App\Tests\Factory\EntityFactory;
use Doctrine\ORM\EntityManagerInterface;

/**
 * A collection of helper methods for test classes.
 */
trait TestUtilities
{
    /**
     * Resolve a service from the container.
     *
     * @param string $key
     *
     * @return object|null
     */
    public function resolve($key)
    {
        return $this->container->get($key);
    }

    /**
     * Build an entity factory.
     *
     * @param string $entity
     *
     * @return \Eoa\Tests\Factory\EntityFactory
     */
    public function factory($entity)
    {
        return EntityFactory::factoryForEntity(
            $entity,
            $this->resolve(EntityManagerInterface::class)
        );
    }

    /**
     * Clear the internal doctrine entity cache.
     *
     * Doctrine will cache the entities created by the EntityFactory during
     * the 'arrange' portion of a test.  This can sometimes have unintended
     * consequences for the business logic being tested. Use this method
     * after all fixtures have been created to prevent side effects.
     *
     * @return void
     */
    protected function clearDoctrineCache()
    {
        $em = $this->resolve(EntityManagerInterface::class);
        $em->getUnitOfWork()->clear();
    }
}
