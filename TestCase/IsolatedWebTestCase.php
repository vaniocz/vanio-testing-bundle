<?php
namespace Vanio\TestingBundle\TestCase;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Vanio\Stdlib\Objects;

abstract class IsolatedWebTestCase extends WebTestCase
{
    /** A connection name which will be isolated during kernel reboots */
    const CONNECTION_NAME = 'default';

    /** @var Connection|null */
    private static $connection;

    /** @var ReferenceRepository|null */
    private static $referenceRepository;

    /** @var bool */
    private static $fixturesLoaded;

    /** @var bool */
    private static $fixturesSavePointCreated;

    /**
     * @beforeClass
     */
    public static function setUpBeforeIsolatedTestCase()
    {
        self::$connection = null;
        self::$referenceRepository = null;
        self::$fixturesLoaded = false;
        self::$fixturesSavePointCreated = false;

        if (method_exists(static::class, 'setUpFixturesForClass')) {
            static::{'setUpFixturesForClass'}();
        }

        if (self::$fixturesLoaded) {
            self::$connection->createSavepoint('fixtures');
            self::$fixturesSavePointCreated = true;
        }
    }

    /**
     * @afterClass
     */
    public static function tearDownAfterIsolatedTestCase()
    {
        static::ensureKernelShutdown();

        if (self::$connection) {
            while (self::$connection->getTransactionNestingLevel()) {
                self::$connection->rollBack();
            }
        }
    }

    /**
     * Keeps the same connection between kernel reboots.
     * @param array $options
     */
    protected static function bootKernel(array $options = [])
    {
        parent::bootKernel($options);

        if (self::$connection) {
            self::replaceConnection(self::$connection);
        } else {
            self::$connection = self::getConnection();
        }

        self::$connection->beginTransaction();
    }

    /**
     * DoctrineBundle automatically closes connections on shutdown and this behavior is not configurable.
     * This cheat temporarily removes the connection service IDs from frozen container before it's shutting down.
     */
    protected static function ensureKernelShutdown()
    {
        if (!self::$connection || !self::isKernelInitialized()) {
            return;
        } elseif (self::$fixturesSavePointCreated) {
            self::$connection->rollbackSavepoint('fixtures');
        } elseif (self::$connection->isTransactionActive()) {
            self::$connection->rollBack();
        }

        $parameters = &self::getContainerParameters();
        $connections = $parameters['doctrine.connections'];
        unset($parameters['doctrine.connections']);
        parent::ensureKernelShutdown();
        $parameters['doctrine.connections'] = $connections;
    }

    /**
     * @param string[] $classes
     * @param string $entityManagerName
     * @return ReferenceRepository
     */
    protected static function loadFixtures(array $classes, string $entityManagerName = 'default'): ReferenceRepository
    {
        self::ensureKernelInitialized();
        $loader = new ContainerAwareLoader(self::getContainer());

        foreach ($classes as $class) {
            self::loadFixture($loader, $class);
        }

        $ormExecutor = new ORMExecutor(self::getEntityManager($entityManagerName));

        if (self::$referenceRepository) {
            $ormExecutor->setReferenceRepository(self::$referenceRepository);
        } else {
            self::$referenceRepository = $ormExecutor->getReferenceRepository();
        }

        $ormExecutor->execute($loader->getFixtures(), true);
        self::$fixturesLoaded = true;

        return self::$referenceRepository;
    }

    protected static function getConnection(string $name = null): Connection
    {
        return self::getContainer()->get(sprintf('doctrine.dbal.%s_connection', $name ?? self::CONNECTION_NAME));
    }

    protected static function getEntityManager(string $name = 'default'): EntityManager
    {
        return self::getContainer()->get("doctrine.orm.{$name}_entity_manager");
    }

    protected static function getEntityRepository(
        string $entityName,
        string $entityManagerName = 'default'
    ): EntityRepository {
        return self::getEntityManager($entityManagerName)->getRepository($entityName);
    }

    private static function loadFixture(Loader $loader, string $fixtureClass)
    {
        /** @var FixtureInterface $fixture */
        $fixture = new $fixtureClass;

        if ($loader->hasFixture($fixture)) {
            return;
        } elseif ($fixture instanceof ContainerAwareInterface) {
            $fixture->setContainer(self::getContainer());
        }

        $loader->addFixture($fixture);

        if ($fixture instanceof DependentFixtureInterface) {
            foreach ($fixture->getDependencies() as $dependency) {
                self::loadFixture($loader, $dependency);
            }
        }
    }

    /**
     * Replaces connection in container with the given connection while keeping old listeners in it's event manager.
     * @param Connection $connection
     */
    private static function replaceConnection(Connection $connection)
    {
        $eventManager = $connection->getEventManager();
        $oldListeners = self::getConnection()->getEventManager()->getListeners();
        $newListeners = $eventManager->getListeners();

        foreach ($newListeners as $event => $listeners) {
            foreach ($listeners as $listener) {
                $eventManager->removeEventListener($event, $listener);
            }
        }

        foreach ($oldListeners as $event => $listeners) {
            foreach ($listeners as $listener) {
                $eventManager->addEventListener($event, $listener);
            }
        }

        self::setContainerService(sprintf('doctrine.dbal.%s_connection', self::CONNECTION_NAME), $connection);
    }

    private static function &getContainerParameters(): array
    {
        return Objects::getPropertyValue(self::getContainer()->getParameterBag(), 'parameters', ParameterBag::class);
    }

    private static function setContainerService(string $id, $service)
    {
        $services = &Objects::getPropertyValue(self::getContainer(), 'services', Container::class);
        $services[$id] = $service;
    }
}
