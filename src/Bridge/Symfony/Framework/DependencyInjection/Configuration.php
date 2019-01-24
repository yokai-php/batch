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
            ->end()
        ;

        return $treeBuilder;
    }
}
