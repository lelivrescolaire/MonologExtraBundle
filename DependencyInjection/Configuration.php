<?php

namespace LLS\Bundle\MonologExtraBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('lls_monolog_extra');

        $rootNode
            ->fixXmlConfig('handler')
            ->append($this->getHandlersNode());

        return $treeBuilder;
    }

    protected function getHandlersNode()
    {
        $treeBuilder = new TreeBuilder();

        $node = $treeBuilder->root('handlers');
        $node
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->children()
                    ->scalarNode('type')
                        ->isRequired()
                        ->treatNullLike('null')
                    ->end()

                    // SQS handler

                    ->scalarNode('queue')->end()

                    // Common

                    ->scalarNode('level')
                        ->defaultValue('INFO')
                    ->end()
                    ->booleanNode('bubble')
                        ->defaultTrue()
                    ->end()
                ->end()
                    ->validate()
                        ->ifTrue(function ($v) {
                            return 'sqs' === $v['type'] && empty($v['queue']);
                        })
                        ->thenInvalid('SQS Handler requires a valid queue identifier.')
                    ->end()
            ->end();

        return $node;
    }
}
