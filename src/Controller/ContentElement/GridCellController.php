<?php

declare(strict_types=1);

/*
 * This file is part of erdmannfreunde/contao-grid-bundle.
 *
 * (c) Erdmann & Freunde <https://erdmann-freunde.de>
 *
 * @license MIT
 */

namespace ErdmannFreunde\ContaoGridBundle\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Eine Spalte im Grid: gibt nur ihre Kinder aus, ohne eigene Ebene im Markup.
 * Das Zell-div erzeugt das Grid, nicht die Spalte selbst.
 */
#[AsContentElement('grid_cell', category: 'euf_grid', nestedFragments: true)]
class GridCellController extends AbstractContentElementController
{
    protected function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        return $template->getResponse();
    }
}
