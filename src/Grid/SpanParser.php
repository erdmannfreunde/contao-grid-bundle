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

use Contao\StringUtil;
use ErdmannFreunde\ContaoGridBundle\GridClasses;

/**
 * Liest die Geometrie eines Elements aus seinen Klassen.
 *
 * Die Klassen sind die Einstellung: col-{viewport}-{n} und row-{viewport}-{n}
 * in grid_columns, col-start-{viewport}-{n} und row-start-{viewport}-{n} in
 * grid_options. Alles andere wird nicht angefasst.
 */
final readonly class SpanParser
{
    public function __construct(private GridClasses $gridClasses)
    {
    }

    /**
     * @return array<string, array<string, int>> Achse => Viewport => Wert
     */
    public function parse(string|null $gridColumns, string|null $gridOptions): array
    {
        $klassen = [...$this->toArray($gridColumns), ...$this->toArray($gridOptions)];

        $geometrie = ['col' => [], 'row' => [], 'col-start' => [], 'row-start' => []];
        $viewports = $this->gridClasses->getViewports();

        foreach ($klassen as $klasse) {
            foreach (array_keys($geometrie) as $praefix) {
                foreach ($viewports as $viewport) {
                    if (preg_match('/^'.preg_quote($praefix, '/').'-'.preg_quote($viewport, '/').'-(\d+)$/', $klasse, $treffer)) {
                        $geometrie[$praefix][$viewport] = (int) $treffer[1];
                    }
                }
            }
        }

        return $geometrie;
    }

    /**
     * Ausrichtung ohne Viewport-Ebene: align-center, justify-end.
     *
     * Die viewport-eigenen Varianten (justify-md-end) bleiben außen vor — der
     * Editor bedient sie nicht und darf sie deshalb auch nicht anfassen.
     *
     * @return array<string, string> Präfix => Richtung
     */
    public function alignment(string|null $gridOptions): array
    {
        $werte = [];

        foreach ((array) $this->gridClasses->getPositioning() as $praefix) {
            $werte[(string) $praefix] = '';
        }

        foreach ($this->toArray($gridOptions) as $klasse) {
            foreach (array_keys($werte) as $praefix) {
                foreach ((array) $this->gridClasses->getDirections() as $richtung) {
                    if ($klasse === $praefix.'-'.$richtung) {
                        $werte[$praefix] = (string) $richtung;
                    }
                }
            }
        }

        return $werte;
    }

    /**
     * Wert, der an diesem Viewport gilt — der nächstkleinere gesetzte Wert, sonst der Standard.
     */
    public function effective(array $achse, string $viewport, int $standard): int
    {
        $wert = $standard;

        foreach ($this->gridClasses->getViewports() as $kandidat) {
            if (isset($achse[$kandidat])) {
                $wert = $achse[$kandidat];
            }

            if ($kandidat === $viewport) {
                break;
            }
        }

        return $wert;
    }

    /**
     * @return array<string>
     */
    private function toArray(string|null $wert): array
    {
        if (!$wert) {
            return [];
        }

        $klassen = StringUtil::deserialize($wert, true);

        return array_values(array_filter(array_map('strval', $klassen)));
    }
}
