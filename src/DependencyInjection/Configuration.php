<?php

declare(strict_types=1);

/*
 * This file is part of erdmannfreunde/contao-grid-bundle.
 *
 * (c) Erdmann & Freunde <https://erdmann-freunde.de>
 *
 * @license MIT
 */

namespace ErdmannFreunde\ContaoGridBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('erdmannfreunde_contao_grid');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->booleanNode('translated_labels')
                    ->defaultFalse()
                ->end()
                ->scalarNode('row_class')
                    ->defaultValue('row')
                ->end()
                ->arrayNode('columns')
                    ->integerPrototype()->end()
                    ->defaultValue(range(1, 12))
                ->end()
                ->booleanNode('columns_no_column')
                    ->defaultTrue()
                ->end()
                ->arrayNode('viewports')
                    ->scalarPrototype()->end()
                    ->defaultValue(['xs', 'sm', 'md', 'lg', 'xl'])
                ->end()
                ->booleanNode('viewports_no_viewport')
                    ->defaultTrue()
                ->end()
                ->arrayNode('column_prefixes')
                    ->scalarPrototype()->end()
                    ->defaultValue(['col', 'row-span'])
                ->end()
                ->arrayNode('options_prefixes')
                    ->scalarPrototype()->end()
                    ->defaultValue(['col-start', 'row-start'])
                ->end()
                ->arrayNode('pulls')
                    ->scalarPrototype()->end()
                    ->defaultValue(['pull-left', 'pull-right'])
                ->end()
                ->arrayNode('positioning')
                    ->scalarPrototype()->end()
                    ->defaultValue(['align', 'justify'])
                ->end()
                ->arrayNode('directions')
                    ->scalarPrototype()->end()
                    ->defaultValue(['start', 'center', 'end', 'stretch'])
                ->end()
                ->arrayNode('options_columns')
                    ->integerPrototype()->end()
                    ->defaultValue(range(1, 12))
                ->end()
                ->scalarNode('group_class')
                    ->defaultValue('group')
                ->end()
                ->arrayNode('group_tag')
                    ->scalarPrototype()->end()
                    ->defaultValue(['div', 'span', 'p', 'button'])
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
