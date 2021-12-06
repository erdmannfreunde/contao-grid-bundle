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

use ErdmannFreunde\ContaoGridBundle\EventListener\DataContainer\TranslatedLabelsListener;
use ErdmannFreunde\ContaoGridBundle\GridClasses;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

final class ErdmannFreundeContaoGridExtension extends Extension
{
    public function getAlias(): string
    {
        return 'erdmannfreunde_contao_grid';
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $loader->load('services.yml');

        $translatedValues = (bool) array_shift($config);

        $definition = $container->getDefinition(TranslatedLabelsListener::class);
        $definition->setArgument(0, $translatedValues);

        $definition = $container->getDefinition(GridClasses::class);
        $definition->setArguments(array_values($config));
    }
}
