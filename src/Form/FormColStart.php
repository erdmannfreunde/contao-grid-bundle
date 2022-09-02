<?php

declare(strict_types=1);

/*
 * This file is part of erdmannfreunde/contao-grid-bundle.
 *
 * (c) Erdmann & Freunde <https://erdmann-freunde.de>
 *
 * @license MIT
 */

namespace ErdmannFreunde\ContaoGridBundle\Form;

use Contao\BackendTemplate;
use Contao\Widget;
use Contao\System;
use Symfony\Component\HttpFoundation\Request;

class FormColStart extends Widget
{
    protected $strTemplate = 'form_colStart';

    public function validate(): void
    {
    }

    public function parse($arrAttributes = null)
    {
        // Return a wildcard in the back end
        $request = System::getContainer()->get('request_stack')->getCurrentRequest();

        if ($request && System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest($request)) {
            /** @var BackendTemplate|object $objTemplate */
            $objTemplate = new BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### '.$GLOBALS['TL_LANG']['FFL']['colStart'][0].' ###';

            return $objTemplate->parse();
        }

        return parent::parse($arrAttributes);
    }

    public function generate()
    {
        return '';
    }
}
