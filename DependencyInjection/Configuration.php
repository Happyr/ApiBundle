<?php

namespace Happyr\ApiBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('happyr_api');

        $rootNode
            ->children()
                ->arrayNode('wsse')
                    ->canBeEnabled()
                    ->children()
                        ->booleanNode('debug')->defaultFalse()->end()
                        ->arrayNode('debug_roles')->prototype('scalar')->end()->end()
                        ->scalarNode('user_provider')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('cache_service')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('lifetime')->cannotBeEmpty()->defaultValue('300')->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
