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
use Contao\DataContainer;

final class RegisterFieldsInPaletteListener
{
    /**
     * @Callback(table="tl_content", target="config.onload", priority=-10)
     */
    public function onLoadContentCallback(): void
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

    /**
     * @Callback(table="tl_form_field", target="config.onload", priority=-10)
     */
    public function onLoadFormFieldCallback(): void
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
