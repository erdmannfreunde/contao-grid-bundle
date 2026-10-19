<?php

declare(strict_types=1);

/*
 * This file is part of erdmannfreunde/contao-grid-bundle.
 *
 * (c) Erdmann & Freunde <https://erdmann-freunde.de>
 *
 * @license MIT
 */

namespace ErdmannFreunde\ContaoGridBundle\Widget;

use Contao\StringUtil;
use Contao\System;
use Contao\Widget;
use ErdmannFreunde\ContaoGridBundle\Grid\GridEditorData;
use ErdmannFreunde\ContaoGridBundle\GridClasses;

/**
 * Der Raster-Editor im Grid-Element.
 *
 * Zeigt eine Zelle je Kind, je Viewport entweder ein Muster (alle gleich breit)
 * oder freie Breiten. Der eigene Wert des Feldes ist das Muster je Viewport;
 * die Breiten der Kinder wandern über ein verstecktes Feld zum onsubmit-Callback,
 * der sie als Klassen in die Kinder schreibt.
 *
 * Aus der Inhaltsliste heraus öffnet dieses Formular im Overlay, der Editor ist
 * dort die Hauptfläche.
 */
class GridEditorWidget extends Widget
{
    protected $strTemplate = 'be_widget';

    protected $blnSubmitInput = true;

    public function __construct($arrAttributes = null)
    {
        parent::__construct($arrAttributes);

        $this->preserveTags = true;
        $this->decodeEntities = true;
    }

    public function validate(): void
    {
        $muster = $_POST[$this->strName] ?? [];
        $erlaubt = $this->editorData()->patterns();
        $sauber = [];

        foreach ($this->gridClasses()->getViewports() as $viewport) {
            $wert = (string) ($muster[$viewport] ?? '');

            if ('' !== $wert && \in_array($wert, $erlaubt, true)) {
                $sauber[$viewport] = $wert;
            }
        }

        $this->varValue = $sauber ? serialize($sauber) : null;
    }

    public function generate(): string
    {
        // Die Liste lädt ihre eigenen Dateien, dieses Formular öffnet aber auch
        // ohne sie — etwa im Overlay oder direkt im Elementbaum
        $GLOBALS['TL_CSS']['euf_grid_backend'] = 'bundles/erdmannfreundecontaogrid/grid_backend.css';
        $GLOBALS['TL_JAVASCRIPT']['euf_grid_editor'] = 'bundles/erdmannfreundecontaogrid/grid_editor.js';

        $daten = $this->editorData()->build((int) ($this->currentRecord ?? 0), $this->strName, $this->varValue);

        $html = \sprintf(
            '<div class="euf-grid-editor" data-euf-grid-editor data-euf-grid-config="%s">',
            StringUtil::specialchars(json_encode($daten, JSON_THROW_ON_ERROR)),
        );

        // Fällt das JavaScript aus, bleibt die gespeicherte Aufteilung erhalten
        foreach ((array) $daten['pattern'] as $viewport => $wert) {
            $html .= \sprintf(
                '<input type="hidden" name="%s[%s]" value="%s">',
                $this->strName,
                StringUtil::specialchars((string) $viewport),
                StringUtil::specialchars((string) $wert),
            );
        }

        $html .= '<input type="hidden" name="euf_grid_cells" value="">';
        $html .= '</div>';

        return $html;
    }

    private function editorData(): GridEditorData
    {
        return System::getContainer()->get(GridEditorData::class);
    }

    private function gridClasses(): GridClasses
    {
        return System::getContainer()->get(GridClasses::class);
    }
}
