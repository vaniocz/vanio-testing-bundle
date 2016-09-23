<?php
namespace Vanio\TestingBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag;

/**
 * @method Extension getExtension(string $name)
 */
class TestContainerBuilder extends ContainerBuilder
{
    /**
     * Compiles the container without unsetting configuration of bundle extensions for testing purposes.
     */
    public function compile()
    {
        $compiler = $this->getCompiler();

        if ($this->isTrackingResources()) {
            foreach ($compiler->getPassConfig()->getPasses() as $pass) {
                $this->addObjectResource($pass);
            }
        }

        $compiler->compile($this);

        if ($this->isTrackingResources()) {
            foreach ($this->getDefinitions() as $definition) {
                $class = $definition->getClass();

                if ($class && $definition->isLazy() && class_exists($class)) {
                    $this->addClassResource(new \ReflectionClass($class));
                }
            }
        }

        $this->parameterBag->resolve();
        $this->parameterBag = new FrozenParameterBag($this->parameterBag->all());
    }

    public function processExtensionConfig(string $name): array
    {
        return (new Processor)->processConfiguration(
            $this->getExtension($name)->getConfiguration([], $this),
            $this->getExtensionConfig($name)
        );
    }
}
