<?php

declare(strict_types=1);

/*
 * This file is part of erdmannfreunde/contao-grid-bundle.
 *
 * (c) Erdmann & Freunde <https://erdmann-freunde.de>
 *
 * @license MIT
 */

namespace ErdmannFreunde\ContaoGridBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;

#[AsCallback(table: 'tl_layout', target: 'fields.addEuFGridCss.xlabel')]
final class LayoutGridCssDeprecationListener
{
    public function __invoke(): string
    {
        // The notice is hidden via CSS as long as the checkbox is not checked
        $GLOBALS['TL_CSS'][] = 'bundles/erdmannfreundecontaogrid/grid_backend.css';

        return '<span class="tl_info tl_grid_deprecation">'.$GLOBALS['TL_LANG']['tl_layout']['addEuFGridCssDeprecated'].'</span>';
    }
}
