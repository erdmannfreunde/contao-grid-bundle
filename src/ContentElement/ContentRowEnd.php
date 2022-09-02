<?php

declare(strict_types=1);

/*
 * This file is part of erdmannfreunde/contao-grid-bundle.
 *
 * (c) Erdmann & Freunde <https://erdmann-freunde.de>
 *
 * @license MIT
 */

namespace ErdmannFreunde\ContaoGridBundle\ContentElement;

use Contao\BackendTemplate;
use Contao\ContentElement;
use Contao\System;
use Symfony\Component\HttpFoundation\Request;

class ContentRowEnd extends ContentElement
{
    protected $strTemplate = 'ce_rowEnd';

    public function compile(): void
    {
    }

    public function generate()
    {
    $request = System::getContainer()->get('request_stack')->getCurrentRequest();

    if ($request && System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest($request)) {
        $this->Template = new BackendTemplate('be_wildcard');
            $this->Template->wildcard = '### E&F GRID: Reihe Ende ###';

            return $this->Template->parse();
        }

        return parent::generate();
    }
}
