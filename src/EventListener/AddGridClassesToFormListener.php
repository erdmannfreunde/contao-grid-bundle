<?php

declare(strict_types=1);

/*
 * This file is part of erdmannfreunde/contao-grid-bundle.
 *
 * (c) Erdmann & Freunde <https://erdmann-freunde.de>
 *
 * @license MIT
 */

namespace ErdmannFreunde\ContaoGridBundle\EventListener;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\Form;
use Contao\StringUtil;
use Contao\Widget;

/**
 * @Hook("loadFormField")
 */
final class AddGridClassesToFormListener
{
    public function __invoke(Widget $objWidget, string $formId, array $data, Form $form): Widget
    {
        $strClasses = '';

        // Bei diesen ContentElementen soll nichts verändert werden
        $arrWrongFields = ['rowStart', 'rowEnd', 'colEnd', 'html', 'fieldsetfsStop'];

        if (!\in_array($objWidget->type, $arrWrongFields, true) && (isset($objWidget->grid_columns) || isset($objWidget->grid_options))) {
            if ($objWidget->grid_columns) {
                $arrGridClasses = StringUtil::deserialize($objWidget->grid_columns);

                foreach ($arrGridClasses as $class) {
                    $strClasses .= $class.' ';
                }
            }

            // Weitere Optionen Klassen auslesen und in String speichern
            if ($objWidget->grid_options) {
                $arrGridClasses = StringUtil::deserialize($objWidget->grid_options);

                foreach ($arrGridClasses as $class) {
                    $strClasses .= $class.' ';
                }
            }

            // Klassen anfügen
            if ('fieldset' === $objWidget->type || 'submit' === $objWidget->type) {
                $objWidget->class = $strClasses;
            } else {
                $objWidget->prefix .= ' '.$strClasses;
            }
        }

        return $objWidget;
    }
}
