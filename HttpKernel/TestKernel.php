<?php
namespace Vanio\TestingBundle\HttpKernel;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;
use Vanio\TestingBundle\DependencyInjection\TestContainerBuilder;

abstract class TestKernel extends Kernel
{
    /** @var TestContainerBuilder */
    protected $container;

    public function __construct(string $environment = 'default', bool $debug = true)
    {
        parent::__construct($environment, $debug);
    }

    /**
     * @return BundleInterface[]
     */
    public function registerBundles(): array
    {
        return require sprintf('%s/Tests/Functional/app/%s/bundles.php', $this->rootDir, $this->environment);
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load("{$this->rootDir}/Tests/Functional/app/{$this->environment}/config.yml");
    }

    public function getCacheDir(): string
    {
        return "{$this->rootDir}/var/cache/{$this->environment}";
    }

    public function getLogDir(): string
    {
        return "{$this->rootDir}/var/logs/{$this->environment}";
    }

    /**
     * Initializes the container while keeping the container builder instance for testing purposes.
     */
    protected function initializeContainer()
    {
        $this->container = $this->buildContainer();
        $this->container->compile();
        $this->container->set('kernel', $this);
    }

    protected function getContainerBuilder(): TestContainerBuilder
    {
        return new TestContainerBuilder(new ParameterBag($this->getKernelParameters()));
    }
}
