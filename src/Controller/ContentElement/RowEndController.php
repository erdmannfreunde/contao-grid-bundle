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

use Contao\BackendTemplate;
use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\ServiceAnnotation\ContentElement;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @ContentElement("rowEnd", category="euf_grid")
 */
class RowEndController extends AbstractContentElementController
{
    protected function getResponse(Template $template, ContentModel $model, Request $request): Response
    {
        if ($this->get('contao.routing.scope_matcher')->isBackendRequest($request)) {
            $template = new BackendTemplate('be_wildcard');
            $template->wildcard = '### E&F GRID: Reihe Ende ###';

            return $template->getResponse();
        }

        return $template->getResponse();
    }
}
