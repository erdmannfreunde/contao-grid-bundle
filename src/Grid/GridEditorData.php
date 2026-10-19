<?php

declare(strict_types=1);

/*
 * This file is part of erdmannfreunde/contao-grid-bundle.
 *
 * (c) Erdmann & Freunde <https://erdmann-freunde.de>
 *
 * @license MIT
 */

namespace ErdmannFreunde\ContaoGridBundle\Grid;

use Contao\ContentModel;
use Contao\StringUtil;
use ErdmannFreunde\ContaoGridBundle\GridClasses;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Baut den Zustand des Raster-Editors für ein Grid zusammen.
 *
 * Dieselben Daten speisen beide Orte: das Feld im Elementformular und das
 * Panel in der Inhaltsliste.
 */
final readonly class GridEditorData
{
    /**
     * Die Texte des Editors. Sie stehen in den Sprachdateien und reisen mit der
     * Konfiguration ins JavaScript — dort wird %s der Reihe nach ersetzt.
     */
    private const TEXTS = [
        'view', 'pattern', 'patternStacked', 'patternEqual', 'patternFree',
        'width', 'height', 'colStart', 'rowStart', 'auto', 'autoHint',
        'default', 'close', 'closeMenu', 'openMenu', 'decrease', 'increase',
        'menuFree', 'menuPattern', 'replaceOne', 'replaceMany',
        'summaryInherited', 'rowOne', 'rowMany', 'summaryStacked',
        'summaryEqual', 'summaryFull', 'summaryFree', 'summaryFreeLast',
    ];

    public function __construct(
        private GridClasses $gridClasses,
        private SpanParser $spanParser,
        private TranslatorInterface $translator,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function build(int $gridId, string $feldname, mixed $gespeichertesMuster = null): array
    {
        $kinder = $this->kinder($gridId);

        // Je nach Aufrufer kommt der Wert als serialisierter String oder bereits als Array
        $muster = \is_array($gespeichertesMuster) ? $gespeichertesMuster : StringUtil::deserialize($gespeichertesMuster, true);

        // Viewports, für die Kinder eigene Werte tragen, sind "frei" — sonst
        // sähe der Editor leer aus und würde die Werte beim Speichern verwerfen
        foreach ($this->gridClasses->getViewports() as $viewport) {
            if (isset($muster[$viewport])) {
                continue;
            }

            foreach ($kinder as $kind) {
                if (isset($kind['col'][$viewport]) || isset($kind['rowSpan'][$viewport]) || isset($kind['colStart'][$viewport]) || isset($kind['rowStart'][$viewport])) {
                    $muster[$viewport] = 'frei';
                    break;
                }
            }
        }

        return [
            'name' => $feldname,
            'labels' => $this->texts(),
            'columns' => \count($this->gridClasses->getColumns()),
            'viewports' => $this->viewports(),
            'patterns' => $this->patterns(),
            'positioning' => $this->positioning(),
            'directions' => $this->directions(),
            'pattern' => $muster,
            'cells' => $kinder,
        ];
    }

    /**
     * Nur Aufteilungen, die in der Spaltenzahl glatt aufgehen.
     *
     * @return array<string>
     */
    public function patterns(): array
    {
        $spalten = \count($this->gridClasses->getColumns());
        $muster = [];

        for ($n = 1; $n <= min(6, $spalten); ++$n) {
            if (0 === $spalten % $n) {
                $muster[] = (string) $n;
            }
        }

        $muster[] = 'frei';

        return $muster;
    }

    /**
     * @return array<array<string, mixed>>
     */
    private function kinder(int $gridId): array
    {
        if ($gridId < 1) {
            return [];
        }

        $kinder = [];

        foreach (ContentModel::findBy(['pid=?', 'ptable=?'], [$gridId, 'tl_content'], ['order' => 'sorting']) ?? [] as $kind) {
            $geometrie = $this->spanParser->parse($kind->grid_columns, $kind->grid_options);

            $kinder[] = [
                'id' => (int) $kind->id,
                'type' => (string) ($GLOBALS['TL_LANG']['CTE'][$kind->type][0] ?? $kind->type),
                'label' => $this->label($kind),
                'col' => $geometrie['col'],
                'rowSpan' => $geometrie['row'],
                'colStart' => $geometrie['col-start'],
                'rowStart' => $geometrie['row-start'],
                'alignment' => $this->spanParser->alignment($kind->grid_options),
                'invisible' => (bool) $kind->invisible,
            ];
        }

        return $kinder;
    }

    /**
     * @return array<string, string>
     */
    private function texts(): array
    {
        $texte = [];

        foreach (self::TEXTS as $schluessel) {
            $texte[$schluessel] = (string) ($GLOBALS['TL_LANG']['eufGrid'][$schluessel] ?? $schluessel);
        }

        return $texte;
    }

    /**
     * @return array<array<string, string>>
     */
    private function positioning(): array
    {
        $liste = [];

        foreach ((array) $this->gridClasses->getPositioning() as $praefix) {
            $liste[] = [
                'key' => (string) $praefix,
                'label' => (string) ($GLOBALS['TL_LANG']['eufGrid']['position'][$praefix] ?? $praefix),
            ];
        }

        return $liste;
    }

    /**
     * @return array<array<string, string>>
     */
    private function directions(): array
    {
        $liste = [];

        foreach ((array) $this->gridClasses->getDirections() as $richtung) {
            $liste[] = [
                'key' => (string) $richtung,
                'label' => (string) ($GLOBALS['TL_LANG']['eufGrid']['direction'][$richtung] ?? $richtung),
            ];
        }

        return $liste;
    }

    /**
     * @return array<array<string, string>>
     */
    private function viewports(): array
    {
        $liste = [];

        foreach ($this->gridClasses->getViewports() as $viewport) {
            $liste[] = ['key' => $viewport, 'label' => $this->viewportLabel((string) $viewport)];
        }

        return $liste;
    }

    /**
     * Welche Breite hinter einem Viewport steckt, weiß nur das Theme. Deshalb
     * steht die Beschriftung in den Übersetzungen: Ein Projekt legt eine eigene
     * translations/grid.de.yaml an und schreibt dort hinein, was wirklich gilt.
     */
    private function viewportLabel(string $viewport): string
    {
        $schluessel = 'viewport.'.$viewport;
        $label = $this->translator->trans($schluessel, [], 'grid');

        // Ohne eigenen Eintrag gibt der Übersetzer den Schlüssel zurück
        if ($label === $schluessel) {
            $label = $this->translator->trans('viewport.default', ['%viewport%' => strtoupper($viewport)], 'grid');
        }

        return $label;
    }

    private function label(ContentModel $kind): string
    {
        $headline = StringUtil::deserialize($kind->headline, true);
        $text = (string) ($headline['value'] ?? $kind->title ?? '');

        if (!$text) {
            $text = trim(strip_tags((string) $kind->text));
        }

        return mb_substr($text, 0, 60);
    }
}
