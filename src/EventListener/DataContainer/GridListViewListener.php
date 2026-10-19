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

use Contao\ContentModel;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Contao\Input;
use Contao\System;
use ErdmannFreunde\ContaoGridBundle\Grid\SpanParser;
use ErdmannFreunde\ContaoGridBundle\GridClasses;

/**
 * Die Inhaltsliste eines Grids: Karten im Raster, sonst nichts.
 *
 * Contao rendert die Datensätze als gestapelte Liste (ul > li). Hier bekommt
 * jede Karte ihre Spannweite mit; CSS macht daraus zwölf Spuren. Fällt das
 * JavaScript aus, steht die Liste wie bisher untereinander.
 *
 * Die rechte Spalte entfällt: Suchen und Filtern helfen in einem Grid nicht
 * weiter, und der Platz gehört der Darstellung. Bearbeitet wird über den Stift
 * im Kopf der Liste, der das Elementformular im Overlay öffnet — dort liegt der
 * Raster-Editor. Das JavaScript macht dieses Overlay nur breiter.
 */
final readonly class GridListViewListener
{
    public function __construct(
        private SpanParser $spanParser,
        private GridClasses $gridClasses,
    ) {
    }

    #[AsCallback(table: 'tl_content', target: 'config.onload', priority: -20)]
    public function onLoad(): void
    {
        if (!$this->currentGrid()) {
            return;
        }

        $GLOBALS['TL_DCA']['tl_content']['list']['sorting']['child_record_class'] = 'euf-grid-item';
        $GLOBALS['TL_DCA']['tl_content']['list']['sorting']['panelLayout'] = '';

        $this->addSpansToLabel();

        $GLOBALS['TL_CSS'][] = 'bundles/erdmannfreundecontaogrid/grid_backend.css';
        $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/erdmannfreundecontaogrid/grid_backend.js';
    }

    /**
     * Hängt jeder Karte ihre Spannweite an.
     *
     * Ein Datenträger für die ganze Liste bräuchte einen Platz im Turbo-Frame,
     * den es ohne Panel nicht mehr gibt. Am Label hängt der Wert ohnehin am
     * richtigen Datensatz — auch nachdem jemand die Karten verschoben hat.
     */
    private function addSpansToLabel(): void
    {
        $vorher = $GLOBALS['TL_DCA']['tl_content']['list']['label']['label_callback'] ?? null;

        $viewports = $this->gridClasses->getViewports();
        $viewport = (string) (end($viewports) ?: 'md');
        $spalten = \count($this->gridClasses->getColumns());

        $GLOBALS['TL_DCA']['tl_content']['list']['label']['label_callback'] = function (array $row, string $label, DataContainer $dc, array $args = []) use ($vorher, $viewport, $spalten) {
            $ergebnis = $label;

            if (\is_array($vorher)) {
                $ergebnis = System::importStatic($vorher[0])->{$vorher[1]}($row, $label, $dc, $args);
            } elseif (\is_callable($vorher)) {
                $ergebnis = $vorher($row, $label, $dc, $args);
            }

            $geometrie = $this->spanParser->parse($row['grid_columns'] ?? null, $row['grid_options'] ?? null);

            $traeger = \sprintf(
                '<span class="euf-grid-span" hidden data-euf-col="%d" data-euf-row="%d" data-euf-start="%d" data-euf-columns="%d"></span>',
                $this->spanParser->effective($geometrie['col'], $viewport, $spalten),
                $this->spanParser->effective($geometrie['row'], $viewport, 1),
                $this->spanParser->effective($geometrie['col-start'], $viewport, 0),
                $spalten,
            );

            // Im Elternmodus darf das Label ein Array sein: Beschriftung, Vorschau, Zustand
            if (\is_array($ergebnis)) {
                $ergebnis[0] = ($ergebnis[0] ?? '').$traeger;

                return $ergebnis;
            }

            return $ergebnis.$traeger;
        };
    }

    private function currentGrid(): ContentModel|null
    {
        if ('tl_content' !== Input::get('ptable') || Input::get('act')) {
            return null;
        }

        $pid = (int) Input::get('id');

        if ($pid < 1) {
            return null;
        }

        $grid = ContentModel::findById($pid);

        return \in_array($grid?->type, ['grid', 'grid_cell'], true) ? $grid : null;
    }
}
