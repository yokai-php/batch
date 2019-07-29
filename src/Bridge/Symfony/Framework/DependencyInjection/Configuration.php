<?php declare(strict_types=1);

namespace Yokai\Batch\Bridge\Symfony\Framework\DependencyInjection;

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

        //todo append config to $root
        $root
            ->children()
                ->arrayNode('storage')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('filesystem')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('dir')
                                    ->isRequired()
                                    ->cannotBeEmpty()
                                    ->defaultValue('%kernel.project_dir%/var/batch/')
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
