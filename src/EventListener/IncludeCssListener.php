<?php

declare(strict_types=1);

/*
 * This file is part of erdmannfreunde/contao-grid-bundle.
 *
 * (c) Erdmann & Freunde <https://erdmann-freunde.de>
 *
 * @license MIT
 */

namespace ErdmannFreunde\ContaoGridBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\LayoutModel;
use Contao\PageModel;
use Contao\PageRegular;

#[AsHook('getPageLayout')]
final class IncludeCssListener
{
    public function __invoke(PageModel $pageModel, LayoutModel $layoutModel, PageRegular $page): void
    {
        if (!$layoutModel->addEuFGridCss) {
            return;
        }

        trigger_deprecation('erdmannfreunde/contao-grid-bundle', '2.2', 'Using the "addEuFGridCss" option of page layout ID %s to include the grid CSS is deprecated and will no longer work in version 3.0. Add the grid classes to your own theme instead.', $layoutModel->id);

        $GLOBALS['TL_FRAMEWORK_CSS'][] = 'bundles/erdmannfreundecontaogrid/euf_grid.css';
    }
}
