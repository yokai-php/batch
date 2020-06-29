<?php

declare(strict_types=1);

namespace Yokai\Batch\Bridge\Symfony\Framework\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /**
     * @inheritdoc
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $root = ($treeBuilder = new TreeBuilder('yokai_batch'))->getRootNode();

        $root
            ->children()
                ->append($this->storage())
            ->end()
        ;

        return $treeBuilder;
    }

    private function storage(): ArrayNodeDefinition
    {
        $node = ($treeBuilder = new TreeBuilder('storage'))->getRootNode();

        $node
            ->children()
                ->arrayNode('filesystem')
                    ->children()
                        ->scalarNode('dir')
                            ->defaultValue('%kernel.project_dir%/var/batch')
                        ->end()
                        ->append($this->serializer())
                    ->end()
                ->end()
                ->arrayNode('dbal')
                    ->children()
                        ->scalarNode('connection')
                            ->defaultValue('default')
                        ->end()
                        ->arrayNode('options')
                            ->useAttributeAsKey('name')
                            ->scalarPrototype()->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    private function serializer(): ArrayNodeDefinition
    {
        $node = ($treeBuilder = new TreeBuilder('serializer'))->getRootNode();

        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('format')
                    ->defaultValue('json')
                ->end()
                ->scalarNode('service')
                    ->defaultNull()
                ->end()
                ->arrayNode('symfony')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('context')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('common')
                                    ->useAttributeAsKey('name')
                                    ->variablePrototype()->end()
                                ->end()
                                ->arrayNode('serialize')
                                    ->useAttributeAsKey('name')
                                    ->variablePrototype()->end()
                                ->end()
                                ->arrayNode('deserialize')
                                    ->useAttributeAsKey('name')
                                    ->variablePrototype()->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }
}
