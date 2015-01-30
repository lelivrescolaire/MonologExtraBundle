<?php

namespace LLS\Bundle\MonologExtraBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;

use LLS\Bundle\SQSBundle\DependencyInjection\LLSSQSExtension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class LLSMonologExtraExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        if (!empty($config['handlers'])) {
            $this->loadHandlers($container, $config['handlers']);
        }

        if (!empty($config['processors'])) {
            $this->loadProcessors($container, $config['processors']);
        }
    }

    protected function loadHandlers(ContainerBuilder $container, array $config)
    {
        foreach ($config as $name => $attributes) {
            switch ($attributes["type"])
            {
                case 'sqs':
                    $this->loadSQSHandler($container, $name, $attributes);
                    break;
            }
        }

        return $this;
    }

    protected function loadProcessors(ContainerBuilder $container, array $config)
    {
        foreach ($config as $processorName => $handlers) {
            $serviceName = sprintf('lls_monolog_extra.processors.%s', $processorName);
            $service     = $container->getDefinition($serviceName);

            if (!empty($handlers)) {
                foreach ($handlers as $handler) {
                    $service->addTag('monolog.processor', array(
                        'method'  => 'processRecord',
                        'handler' => $handler
                    ));
                }
            } else {
                $service->addTag('monolog.processor', array(
                    'method'  => 'processRecord'
                ));
            }
        }

        return $this;
    }

    protected function loadSQSHandler(ContainerBuilder $container, $name, array $attributes)
    {
        $arguments = array(
            new Reference(
                LLSSQSExtension::getQueueServiceKey($attributes['queue'])
            ),
            $attributes['level'],
            $attributes['bubble']
        );

        $container
            ->setDefinition(
                self::getHandlerServiceKey($name),
                new Definition(
                    $container->getParameter('lls_monolog_extra.sqs_handler.class'),
                    $arguments
                )
            );
    }

    /**
     * Get Handler Service key from it's name
     *
     * @param string $name Service name
     *
     * @return string
     */
    public static function getHandlerServiceKey($name)
    {
        return sprintf('lls_monolog_extra.handlers.%s', $name);
    }
}
