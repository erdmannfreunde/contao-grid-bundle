<?php

declare(strict_types=1);

/*
 * This file is part of erdmannfreunde/contao-grid-bundle.
 *
 * (c) Erdmann & Freunde <https://erdmann-freunde.de>
 *
 * @license MIT
 */

namespace ErdmannFreunde\ContaoGridBundle\Tests;

use ErdmannFreunde\ContaoGridBundle\GridClasses;

/**
 * Die Standardkonfiguration als Objekt — dieselben Werte wie in Configuration.
 */
trait GridClassesFixture
{
    /**
     * @param array<string>|null $viewports
     * @param array<int>|null    $columns
     */
    private function gridClasses(array|null $viewports = null, array|null $columns = null): GridClasses
    {
        return new GridClasses(
            'row',
            $columns ?? range(1, 12),
            true,
            $viewports ?? ['xs', 'sm', 'md', 'lg', 'xl'],
            true,
            ['col', 'row-span'],
            ['col-start', 'row-start'],
            ['pull-left', 'pull-right'],
            ['align', 'justify'],
            ['start', 'center', 'end', 'stretch'],
            range(1, 12),
        );
    }
}
