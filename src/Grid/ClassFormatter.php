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

use ErdmannFreunde\ContaoGridBundle\GridClasses;

/**
 * Rechnet aus, welche Klassen in einem Feld stehen sollen.
 *
 * Das Gegenstück zum SpanParser: Der liest Klassen in Zahlen, dieser schreibt
 * Zahlen in Klassen. Beides ohne Datenbank — die beiden Felder eines Elements
 * sind Listen von Wörtern, mehr braucht es hier nicht.
 *
 * Die Regel, die das Bundle Bestandsprojekten schuldet, steht in merge():
 * Angefasst wird nur, wofür der Editor etwas gesagt hat. Alles andere bleibt
 * Wort für Wort stehen — auch handgetippte Klassen fremder Themes.
 */
final readonly class ClassFormatter
{
    public function __construct(private GridClasses $gridClasses)
    {
    }

    /**
     * @param array<string>                          $vorhanden   Die Klassen, die im Feld stehen
     * @param array<string>                          $praefixe    Die Präfixe, die dieses Feld führt
     * @param array<string, array<string, int|null>> $werte       Viewport => Achse => Wert, null heißt "keine Klasse"
     * @param array<string, string>                  $ausrichtung Präfix => Richtung, ohne Viewport-Ebene
     *
     * @return array<string>
     */
    public function merge(array $vorhanden, array $praefixe, array $werte, array $ausrichtung = []): array
    {
        $fremde = [];

        foreach ($vorhanden as $klasse) {
            if (!$this->isManaged((string) $klasse, $praefixe, $werte) && !$this->isManagedAlignment((string) $klasse, $ausrichtung)) {
                $fremde[] = (string) $klasse;
            }
        }

        $eigene = [];

        foreach ($ausrichtung as $praefix => $richtung) {
            if ('' !== $richtung) {
                $eigene[] = $praefix.'-'.$richtung;
            }
        }

        foreach ($werte as $viewport => $achsen) {
            foreach ($praefixe as $praefix) {
                $wert = $achsen[$praefix] ?? null;

                // Die Standardwerte brauchen keine Klasse: eine Zeile hoch,
                // kein eigener Startpunkt
                if (null === $wert || ('row' === $praefix && $wert < 2)) {
                    continue;
                }

                $eigene[] = $praefix.'-'.$viewport.'-'.$wert;
            }
        }

        return array_values(array_unique([...$eigene, ...$fremde]));
    }

    /**
     * Verwaltet ist eine Klasse nur, wenn der Editor für diesen Viewport und
     * diese Achse etwas gesagt hat — auch "nichts" ist dabei eine Aussage.
     *
     * @param array<string>                          $praefixe
     * @param array<string, array<string, int|null>> $werte
     */
    private function isManaged(string $klasse, array $praefixe, array $werte): bool
    {
        foreach ($werte as $viewport => $achsen) {
            foreach ($praefixe as $praefix) {
                if (!\array_key_exists($praefix, $achsen)) {
                    continue;
                }

                if (preg_match('/^'.preg_quote($praefix.'-'.$viewport, '/').'-\d+$/', $klasse)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param array<string, string> $ausrichtung
     */
    private function isManagedAlignment(string $klasse, array $ausrichtung): bool
    {
        foreach (array_keys($ausrichtung) as $praefix) {
            foreach ((array) $this->gridClasses->getDirections() as $richtung) {
                if ($klasse === $praefix.'-'.$richtung) {
                    return true;
                }
            }
        }

        return false;
    }
}
