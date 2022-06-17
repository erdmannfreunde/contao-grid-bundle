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
use Contao\System;
use Contao\Widget;
use ErdmannFreunde\ContaoGridBundle\GridClasses;

class FormRowStart extends Widget
{
    protected $strTemplate = 'form_rowStart';

    public function validate(): void
    {
    }

    public function parse($arrAttributes = null)
    {
        $rowClass = System::getContainer()->get(GridClasses::class)->getRowClass();

        $this->rowClass = $rowClass;

        // Return a wildcard in the back end
        if (TL_MODE === 'BE') {
            $strCustomClasses = '';

            if ($this->strClass) {
                $strCustomClasses .= ', ';
                $strCustomClasses .= str_replace(' ', ', ', $this->strClass);
            }

            /** @var BackendTemplate|object $objTemplate */
            $objTemplate = new BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### E&F GRID: '.$GLOBALS['TL_LANG']['FFL']['rowStart'][0].'  ###';
            $objTemplate->wildcard .= '<div class="tl_content_right tl_gray m12">('.$rowClass.$strCustomClasses.')</div>';

            return $objTemplate->parse();
        }

        return parent::parse($arrAttributes);
    }

    public function generate()
    {
        return '';
    }
}
