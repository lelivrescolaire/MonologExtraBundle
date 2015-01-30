<?php

namespace LLS\Bundle\MonologExtraBundle\Tests\Units\DependencyInjection;

use \mageekguy\atoum\test;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

use LLS\Bundle\MonologExtraBundle\DependencyInjection\LLSMonologExtraExtension as Extension;
use LLS\Bundle\MonologExtraBundle\Tests\Utils\ContainerBuilderTest;

/**
 * Test class for LLSMonologExtraExtension
 *
 * @author Jérémy Jourdin <jeremy.jourdin@lelivrescolaire.fr>
 */
class LLSMonologExtraExtension extends ContainerBuilderTest
{
    /**
     * @var Extension
     */
    protected $extension;

    /**
     * Root name of the configuration
     *
     * @var string
     */
    protected $root;

    public function beforeTestMethod($method)
    {
        parent::beforeTestMethod($method);

        $this->extension = new Extension();
        $this->root      = "lls_monolog_extra";
    }

    public function testGetConfigWithDefaultValues()
    {
        $this->extension->load(array(), $this->container);

        $this
            ->assert

                // Parameters

                ->string($this->container->getParameter($this->root.'.sqs_handler.class'))
                    ->isEqualTo('LLS\Bundle\MonologExtraBundle\Handler\SQSHandler')

                ->string($this->container->getParameter($this->root.'.processors.ip.class'))
                    ->isEqualTo('LLS\Bundle\MonologExtraBundle\Processor\IPRequestProcessor');
    }

    public function testConfigCreateHandlers()
    {
        $configs = array(
            array(
                "handlers" => array(
                    "bar" => array(
                        "type"   => "sqs",
                        "queue"  => "bar_queue",
                        "bubble" => false
                    )
                )
            ),
            array(
                "handlers" => array(
                    "foo" => array(
                        "type"  => "sqs",
                        "queue" => "foo_queue",
                        "level" => "DEBUG"
                    )
                )
            ),
        );

        $this->extension->load($configs, $this->container);

        $this
            ->assert
                ->boolean($this->container->hasDefinition($this->root.'.handlers.bar'))
                    ->isTrue()
                ->object($definition = $this->container->getDefinition($this->root.'.handlers.bar'))
                    ->string($definition->getClass())
                        ->isEqualTo('LLS\Bundle\MonologExtraBundle\Handler\SQSHandler')
                    ->array($arguments = $definition->getArguments())
                            ->hasSize(3)
                        ->object($arguments[0])
                            ->isEqualTo(new Reference('llssqs.queues.bar_queue'))
                        ->string($arguments[1]) // Test default level value
                            ->isEqualTo('INFO')
                        ->boolean($arguments[2])
                            ->isFalse()

                ->boolean($this->container->hasDefinition($this->root.'.handlers.foo'))
                    ->isTrue()
                ->object($definition = $this->container->getDefinition($this->root.'.handlers.foo'))
                    ->string($definition->getClass())
                        ->isEqualTo('LLS\Bundle\MonologExtraBundle\Handler\SQSHandler')
                    ->array($arguments = $definition->getArguments())
                            ->hasSize(3)
                        ->object($arguments[0])
                            ->isEqualTo(new Reference('llssqs.queues.foo_queue'))
                        ->string($arguments[1])
                            ->isEqualTo('DEBUG')
                        ->boolean($arguments[2]) // Test default bubble value
                            ->isTrue();
    }

    public function testConfigCreateProcessorsWithHandlers()
    {
        $configs = array(
            array(
                "processors" => array(
                    "ip" => array('bar', 'foo')
                )
            ),
        );

        $this->extension->load($configs, $this->container);

        $this
            ->assert
                ->boolean($this->container->hasDefinition($this->root.'.processors.ip'))
                    ->isTrue()
                ->object($definition = $this->container->getDefinition($this->root.'.processors.ip'))
                    ->string($definition->getClass())
                        ->isEqualTo('%'.$this->root.'.processors.ip.class%')
                    ->array($tags = $definition->getTag('monolog.processor'))
                        ->hasSize(2)
                        ->array($tags[0])
                            ->isEqualTo(array('method' => 'processRecord', 'handler' => 'bar'))
                        ->array($tags[1])
                            ->isEqualTo(array('method' => 'processRecord', 'handler' => 'foo'));
    }

    public function testConfigCreateProcessorsWithoutHandlers()
    {
        $this->configCreateProcessorWithoutHandlers(array(
            array(
                "processors" => array(
                    "ip" => array()
                )
            ),
        ));
    }

    public function testConfigCreateProcessorsWithNullHandlers()
    {
        $this->configCreateProcessorWithoutHandlers(array(
            array(
                "processors" => array(
                    "ip" => null
                )
            ),
        ));
    }

    protected function configCreateProcessorWithoutHandlers($configs)
    {

        $this->extension->load($configs, $this->container);

        $this
            ->assert
                ->boolean($this->container->hasDefinition($this->root.'.processors.ip'))
                    ->isTrue()
                ->object($definition = $this->container->getDefinition($this->root.'.processors.ip'))
                    ->string($definition->getClass())
                        ->isEqualTo('%'.$this->root.'.processors.ip.class%')
                    ->array($tags = $definition->getTag('monolog.processor'))
                        ->hasSize(1)
                        ->array($tags[0])
                            ->isEqualTo(array('method' => 'processRecord'));
    }

    public function testConfigOverridesServices()
    {
        $configs = array(
            array(
                "handlers" => array(
                    "bar" => array(
                        "type"   => "sqs",
                        "queue"  => "bar_queue",
                        "bubble" => false,
                        "level"  => "WARNING"
                    )
                )
            ),
            array(
                "handlers" => array(
                    "bar" => array(
                        "queue"  => "foo_queue",
                        "bubble" => true
                    )
                )
            ),
        );

        $this->extension->load($configs, $this->container);

        $this
            ->assert

                ->boolean($this->container->hasDefinition($this->root.'.handlers.bar'))
                    ->isTrue()
                ->object($definition = $this->container->getDefinition($this->root.'.handlers.bar'))
                    ->string($definition->getClass())
                        ->isEqualTo('LLS\Bundle\MonologExtraBundle\Handler\SQSHandler')
                    ->array($arguments = $definition->getArguments())
                            ->hasSize(3)
                        ->object($arguments[0])
                            ->isEqualTo(new Reference('llssqs.queues.foo_queue'))
                        ->string($arguments[1])
                            ->isEqualTo('WARNING')
                        ->boolean($arguments[2]) // Test default bubble value
                            ->isTrue();
    }
}
