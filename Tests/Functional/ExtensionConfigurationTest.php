<?php
namespace Vanio\TestingBundle\Tests\Functional;

use Vanio\TestingBundle\DependencyInjection\TestContainerBuilder;
use Vanio\TestingBundle\TestCase\KernelTestCase;

class ExtensionConfigurationTest extends KernelTestCase
{
    function test_extension_configuration()
    {
        $this->assertInstanceOf(TestContainerBuilder::class, static::getContainer());
        $this->assertArraySubset(
            [
                'secret' => 'secret',
                'form' => ['enabled' => false],
                'profiler' => ['enabled' => false],
                'router' => ['enabled' => true],
            ],
            self::getContainer()->processExtensionConfig('framework')
        );
    }
}
