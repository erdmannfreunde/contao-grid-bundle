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

use Contao\LayoutModel;
use Contao\PageModel;
use Contao\PageRegular;

final class IncludeCssListener
{
    public function onGetPageLayout(PageModel $pageModel, LayoutModel $layoutModel, PageRegular $page): void
    {
        if ($layoutModel->addEuFGridCss) {
            $GLOBALS['TL_FRAMEWORK_CSS'][] = 'bundles/erdmannfreundecontaogrid/euf_grid.css';
        }
    }
}
