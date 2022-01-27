<?php

namespace App\Tests\Factory;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Id\AssignedGenerator;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess;

abstract class EntityFactory
{
    public const FACTORY_NAMESPACE = 'App\\Tests\\Factory\\';

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @var \Faker\Generator
     */
    protected $faker;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessor
     */
    protected $propertyAccessor;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * @var \Eoa\Entity\Organization
     */
    protected $organization;

    /**
     * Should the factory execute hooks when creating entities?
     *
     * @var bool
     */
    protected $allowHooks = true;

    /**
     * Should the new entity be instantiated without its constructor?
     *
     * @var bool
     */
    protected $withoutConstructor = false;

    protected function __construct(EntityManagerInterface $entityManager)
    {
        $this->faker = \Faker\Factory::create();
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
        $this->entityManager = $entityManager;
        $this->organization = null;
    }

    /**
     * Define the default attribute values for this entity.
     */
    abstract public function getDefinition(): array;

    /**
     * Retrieve the name of the class to be instantiated.
     */
    abstract public function getClass(): string;

    /**
     * Create a factory instance for an entity type.
     *
     * @return static
     */
    public static function factoryForEntity(string $class, EntityManagerInterface $entityManager)
    {
        $factory = self::resolveFactoryName($class);

        $instance = new $factory($entityManager);

        return $instance->state($instance->getDefinition());
    }

    /**
     * Get the factory name for the given entity.
     */
    public static function resolveFactoryName(string $name): string
    {
        $shortName = (new \ReflectionClass($name))->getShortName();

        return self::FACTORY_NAMESPACE.$shortName.'Factory';
    }

    /**
     * Add values to the factory attribute state.
     *
     * @param array $attributes
     *
     * @return self
     */
    public function state($attributes = [])
    {
        $this->attributes = array_merge($this->attributes, $attributes);

        return $this;
    }

    /**
     * Create a new entity and persist it to the database.
     *
     * @param array $attributes
     *
     * @return mixed
     */
    public function create($attributes = [])
    {
        $entity = $this->make($attributes);

        // Enable custom ID recording if specified by the factory.
        if ($this->forceCustomId()) {
            $class = $this->entityManager->getClassMetadata(get_class($entity));
            $class->setIdGenerator(new AssignedGenerator());
            $class->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);
        }

        // Persist the new entity to the testing database.
        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return $this->allowHooks
            ? $this->postCreate($entity)
            : $entity;
    }

    /**
     * Optionally run some additional setup steps after creating an entity.
     *
     * @return mixed
     */
    public function postCreate($entity)
    {
        return $entity;
    }

    /**
     * Make a new entity in memory without persisting it to the database.
     *
     * @param array $attributes
     *
     * @return mixed
     */
    public function make($attributes = [])
    {
        // Merge the new attributes with our defaults.
        $this->attributes = array_merge($this->attributes, $attributes);

        // Instantiate a new class instance.
        $instance = $this->instantiate($this->getClass(), $this->attributes);

        // Hydrate the entity attributes.
        foreach ($this->attributes as $attribute => $value) {
            try {
                // If we are setting the 'organization' value we will
                // defer to the fluently recorded organization if present
                if ('organization' == $attribute && $this->organization) {
                    $this->propertyAccessor->setValue($instance, 'organization', $this->organization);
                } else {
                    $this->propertyAccessor->setValue($instance, $attribute, $value);
                }
            } catch (NoSuchPropertyException $e) {
                throw new \InvalidArgumentException(\sprintf('Cannot set attribute "%s" for object "%s".', $attribute, get_called_class()), 0, $e);
            }
        }

        return $instance;
    }

    /**
     * Instantiate a new Doctrine Entity, ensuring that the required constructor
     * parameters are in place. Inspired by:
     * https://github.com/zenstruck/foundry/blob/master/src/Instantiator.php#L24.
     *
     * @return mixed
     */
    protected function instantiate(string $class, array &$attributes): object
    {
        $class = new \ReflectionClass($class);
        $constructor = $class->getConstructor();

        if ($this->withoutConstructor || !$constructor || !$constructor->isPublic()) {
            return $class->newInstanceWithoutConstructor();
        }

        $arguments = [];

        foreach ($constructor->getParameters() as $parameter) {
            if (\array_key_exists($parameter, $attributes)) {
                $arguments[] = $attributes[$parameter];
            } elseif ($parameter->isDefaultValueAvailable()) {
                $arguments[] = $parameter->getDefaultValue();
            } else {
                throw new \InvalidArgumentException(\sprintf('Missing constructor argument "%s" for "%s".', $parameter->getName(), $class->getName()));
            }

            // unset attribute so it isn't used when setting object properties
            unset($attributes[$parameter]);
        }

        return $class->newInstance(...$arguments);
    }

    /**
     * Should we enforce the need to specify an ID for this entity type?
     *
     * @return string
     */
    public function forceCustomId()
    {
        return false;
    }

    /**
     * Retrieve the first available record if available, otherwise
     * persist a default entity and use that instead.
     *
     * @return mixed
     */
    public function firstOrDefault()
    {
        $entities = $this->entityManager
            ->createQuery("SELECT e FROM {$this->getClass()} e ORDER BY e.id ASC")
            ->setMaxResults(1)
            ->getResult();

        if (!empty($entities)) {
            return $entities[0];
        }

        return $this->create();
    }

    /**
     * Build an entity factory. This will let us reference other entities when
     * simulating relationships.
     *
     * @param string $entity
     *
     * @return \Eoa\Tests\Factory\EntityFactory
     */
    public function factory($entity)
    {
        return self::factoryForEntity($entity, $this->entityManager);
    }

    /**
     * Prevent the use of hooks when creating entity.
     *
     * @return self
     */
    public function only()
    {
        $this->allowHooks = false;

        return $this;
    }
}
