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

use Contao\CoreBundle\ServiceAnnotation\Callback;

/**
 * @Callback(table="tl_content", target="config.onload")
 */
final class TranslatedLabelsListener
{

    public function __construct(private readonly bool $translatedLabels = false)
    {
    }

    public function __invoke(): void
    {
        if (!$this->translatedLabels) {
            return;
        }

            $GLOBALS['TL_DCA']['tl_content']['fields']['grid_columns']['reference'] = &$GLOBALS['TL_LANG']['MSC']['grid_columns'];
            $GLOBALS['TL_DCA']['tl_content']['fields']['grid_options']['reference'] = &$GLOBALS['TL_LANG']['MSC']['grid_options'];
    }
}
