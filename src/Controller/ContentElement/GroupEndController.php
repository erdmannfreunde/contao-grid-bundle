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
use Symfony\Component\HttpFoundation\Request;
use ErdmannFreunde\ContaoGridBundle\GridClasses;
use Symfony\Component\HttpFoundation\Response;

/**
 * @ContentElement("groupEnd", category="euf_grid")
 */
class GroupEndController extends AbstractContentElementController
{
    public function __construct(private readonly GridClasses $gridClasses)
    {
    }
    
    protected function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        $template->groupTag = $model->group_tag;

        if ($this->container->get('contao.routing.scope_matcher')->isBackendRequest($request)) {
            $template = new BackendTemplate('be_wildcard');
            $template->wildcard = '### E&F GRID: Gruppe Ende ###';

            return $template->getResponse();
        }

        return $template->getResponse();
    }
}
