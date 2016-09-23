<?php
namespace Vanio\TestingBundle\TestCase;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase as BaseKernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Vanio\TestingBundle\DependencyInjection\TestContainerBuilder;

abstract class KernelTestCase extends BaseKernelTestCase
{
    const DEFAULT_KERNEL_OPTIONS = ['environment' => 'default'];

    /** @var array */
    private static $kernelOptions = self::DEFAULT_KERNEL_OPTIONS;

    protected static function bootKernel(array $options = [])
    {
        self::$kernelOptions = $options + static::DEFAULT_KERNEL_OPTIONS;
        parent::bootKernel(self::$kernelOptions);
    }

    /**
     * Reboots kernel using the last known used options (or default).
     */
    protected static function rebootKernel()
    {
        static::bootKernel(self::$kernelOptions);
    }

    protected static function ensureKernelInitialized()
    {
        if (!self::isKernelInitialized()) {
            static::bootKernel();
        }
    }

    protected static function isKernelInitialized(): bool
    {
        return static::$kernel && static::$kernel->getContainer();
    }

    /**
     * @return TestContainerBuilder|ContainerInterface
     */
    protected static function getContainer(): ContainerInterface
    {
        self::ensureKernelInitialized();

        return static::$kernel->getContainer();
    }

    protected function tearDown()
    {
        parent::tearDown();
        static::$kernel = null;
    }
}
