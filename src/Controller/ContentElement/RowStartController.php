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
 * @ContentElement("rowStart", category="euf_grid")
 */
class RowStartController extends AbstractContentElementController
{
    public function __construct(private readonly GridClasses $gridClasses)
    {
    }

    protected function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        $rowClass = $this->gridClasses->getRowClass();

        $template->rowClass = $rowClass;

        if ($this->get('contao.routing.scope_matcher')->isBackendRequest($request)) {
            $model->cssID = StringUtil::deserialize($model->cssID);

            $strCustomClasses = '';
            if ($model->cssID[1]) {
                $strCustomClasses .= ', ';
                $strCustomClasses .= str_replace(' ', ', ', $model->cssID[1]);
            }

            $template = new BackendTemplate('be_wildcard');
            $template->wildcard = '### E&F GRID: '.$GLOBALS['TL_LANG']['FFL']['rowStart'][0].'  ###';
            $template->wildcard .= '<div class="tl_content_right tl_gray m12" style="text-transform: initial">('.$rowClass.$strCustomClasses.')</div>';

            return $template->getResponse();
        }

        return $template->getResponse();
    }
}
