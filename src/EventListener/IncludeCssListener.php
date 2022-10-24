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

use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\LayoutModel;
use Contao\PageModel;
use Contao\PageRegular;

/**
 * @Hook("getPageLayout")
 */
final class IncludeCssListener
{
    public function __invoke(PageModel $pageModel, LayoutModel $layoutModel, PageRegular $page): void
    {
        if (!$layoutModel->addEuFGridCss) {
            return;
        }

        $GLOBALS['TL_FRAMEWORK_CSS'][] = 'bundles/erdmannfreundecontaogrid/euf_grid.css';
    }
}
