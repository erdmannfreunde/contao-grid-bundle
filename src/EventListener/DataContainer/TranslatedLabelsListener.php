<?php

declare(strict_types=1);

/*
 * This file is part of erdmannfreunde/contao-grid-bundle.
 *
 * (c) Erdmann & Freunde <https://erdmann-freunde.de>
 *
 * @license MIT
 */

namespace ErdmannFreunde\ContaoGridBundle\EventListener\DataContainer;

use Contao\DataContainer;

final class TranslatedLabelsListener
{
    private $translatedLabels;

    public function __construct(bool $translatedLabels = false)
    {
        $this->translatedLabels = $translatedLabels;
    }

    public function onLoadContentCallback(DataContainer $dataContainer): void
    {
        if ($this->translatedLabels) {
            $GLOBALS['TL_DCA']['tl_content']['fields']['grid_columns']['reference'] = &$GLOBALS['TL_LANG']['MSC']['grid_columns'];
            $GLOBALS['TL_DCA']['tl_content']['fields']['grid_options']['reference'] = &$GLOBALS['TL_LANG']['MSC']['grid_options'];
        }
    }
}
