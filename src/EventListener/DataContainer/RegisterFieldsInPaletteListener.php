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

final class RegisterFieldsInPaletteListener
{
    public function onLoadContentCallback(DataContainer $dataContainer): void
    {
        foreach ($GLOBALS['TL_DCA']['tl_content']['palettes'] as $k => $palette) {
            if (!\is_array($palette) && false !== strpos($palette, 'cssID')) {
                $GLOBALS['TL_DCA']['tl_content']['palettes'][$k] = str_replace(
                    '{invisible_legend',
                    '{grid_legend},grid_columns,grid_options;{invisible_legend',
                    $GLOBALS['TL_DCA']['tl_content']['palettes'][$k]
                );
            }
        }
    }

    public function onLoadFormFieldCallback(DataContainer $dataContainer): void
    {
        foreach ($GLOBALS['TL_DCA']['tl_form_field']['palettes'] as $k => $palette) {
            if (
                !\is_array($palette) && false !== strpos($palette, 'customTpl')
                && (!\in_array($k, ['html', 'fieldsetfsStop', 'rowStart'], true))
            ) {
                $GLOBALS['TL_DCA']['tl_form_field']['palettes'][$k] = str_replace(
                    '{template_legend:hide}',
                    '{grid_legend},grid_columns,grid_options;{template_legend:hide}',
                    $GLOBALS['TL_DCA']['tl_form_field']['palettes'][$k]
                );
            }
        }
    }
}
