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
use Contao\StringUtil;
use Symfony\Component\HttpFoundation\Request;
use ErdmannFreunde\ContaoGridBundle\GridClasses;
use Symfony\Component\HttpFoundation\Response;

/**
 * @ContentElement("groupStart", category="euf_grid")
 */
class GroupStartController extends AbstractContentElementController
{
    public function __construct(private readonly GridClasses $gridClasses)
    {
    }

    protected function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        $groupClass = $this->gridClasses->getGroupClass();

        $template->groupClass = $groupClass;
        $template->groupTag = $model->group_tag;

        if ($this->container->get('contao.routing.scope_matcher')->isBackendRequest($request)) {
            $model->cssID = StringUtil::deserialize($model->cssID);

            $strCustomClasses = '';
            if ($model->cssID[1]) {
                $strCustomClasses .= ', ';
                $strCustomClasses .= str_replace(' ', ', ', $model->cssID[1]);
            }

            $template = new BackendTemplate('be_wildcard');
            $template->wildcard = '### E&F GRID: '.$GLOBALS['TL_LANG']['FFL']['groupStart'][0].'  ###';
            $template->wildcard .= '<div class="tl_grid_note">'.$groupClass.$strCustomClasses.'</div>';

            return $template->getResponse();
        }

        return $template->getResponse();
    }
}
