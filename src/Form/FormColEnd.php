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

class FormColEnd extends Widget
{
    protected $strTemplate = 'form_colEnd';

    public function validate(): void
    {
    }

    public function parse($arrAttributes = null)
    {
        // Return a wildcard in the back end
        if (TL_MODE === 'BE') {
            /** @var BackendTemplate|object $objTemplate */
            $objTemplate = new BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### '.$GLOBALS['TL_LANG']['FFL']['colEnd'][0].' ###';

            return $objTemplate->parse();
        }

        return parent::parse($arrAttributes);
    }

    public function generate()
    {
        return '';
    }
}
