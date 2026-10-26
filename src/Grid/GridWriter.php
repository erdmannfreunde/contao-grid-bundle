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
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\CoreBundle\Security\DataContainer\UpdateAction;
use Contao\StringUtil;
use Contao\Versions;
use ErdmannFreunde\ContaoGridBundle\GridClasses;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Schreibt die im Raster-Editor gesetzte Geometrie in die Kinder eines Grids.
 *
 * Die einzige Stelle, an der das Bundle Datensätze schreibt, die nicht der
 * bearbeitete sind. Deshalb: Rechteprüfung je Kind, eine Version je geändertem
 * Datensatz, und zwei Regeln zum Schutz vorhandener Daten:
 *
 * 1. Klassen, die der Editor nicht verwaltet, bleiben unangetastet.
 * 2. Verwaltet ist nur, was der Editor für diesen Viewport auch mitgeteilt hat.
 *
 * Breite und Höhe stehen in grid_columns, die Startwerte in grid_options — so,
 * wie die beiden Felder ihre Klassen seit jeher aufteilen.
 */
final readonly class GridWriter
{
    /**
     * Höchste Höhe, die der Editor vergibt. Darüber hinaus bleibt das
     * Mehrfach-Select im Expertenbereich.
     */
    public const MAX_ROW_SPAN = 6;

    private const FIELDS = [
        'grid_columns' => ['col', 'row'],
        'grid_options' => ['col-start', 'row-start'],
    ];


    public function __construct(
        private Security $security,
        private GridClasses $gridClasses,
        private ClassFormatter $classFormatter,
    ) {
    }

    /**
     * @param array<string, string>        $muster Viewport => Musterschlüssel oder "frei"
     * @param array<int, array<string, mixed>> $zellen Kind-ID => ["viewports" => Viewport => Achse => Wert, Positionierung => Richtung]
     */
    public function apply(int $gridId, array $muster, array $zellen): int
    {
        $spalten = \count($this->gridClasses->getColumns());
        $verwaltet = [];

        foreach ($this->gridClasses->getViewports() as $viewport) {
            if ('' !== (string) ($muster[$viewport] ?? '')) {
                $verwaltet[] = $viewport;
            }
        }

        $geaendert = 0;
        $kinder = ContentModel::findBy(['pid=?', 'ptable=?'], [$gridId, 'tl_content'], ['order' => 'sorting']);

        // Weiter als bis zur letzten Zeile kann keine Zelle anfangen
        $maxZeile = max(1, $kinder?->count() ?? 1);

        foreach ($kinder ?? [] as $kind) {
            $werte = [];

            foreach ($verwaltet as $viewport) {
                $wert = (string) $muster[$viewport];

                // Ohne eigenen Wert fallen die Klassen dieser Ansicht weg,
                // damit die Vererbung von unten wieder greift
                if ('erbt' === $wert) {
                    $werte[$viewport] = [
                        'col' => null,
                        'row' => null,
                        'col-start' => null,
                        'row-start' => null,
                    ];

                    continue;
                }

                if ('frei' === $wert) {
                    $eigen = $zellen[$kind->id]['viewports'][$viewport] ?? null;

                    // Ohne Angabe bleibt stehen, was gespeichert ist
                    if (null === $eigen) {
                        continue;
                    }

                    $werte[$viewport] = [
                        'col' => $this->grenze($eigen['col'] ?? null, $spalten),
                        'row' => $this->grenze($eigen['rowSpan'] ?? null, self::MAX_ROW_SPAN),
                        'col-start' => $this->grenze($eigen['colStart'] ?? null, $spalten),
                        'row-start' => $this->grenze($eigen['rowStart'] ?? null, $maxZeile),
                    ];

                    continue;
                }

                // Ein Muster besitzt den Viewport allein: Höhe und Startwerte
                // blieben sonst stehen und zerlegten die Aufteilung still
                $werte[$viewport] = [
                    'col' => intdiv($spalten, max(1, (int) $wert)),
                    'row' => null,
                    'col-start' => null,
                    'row-start' => null,
                ];
            }

            $ausrichtung = $this->alignment($zellen[$kind->id] ?? []);

            if (($werte || $ausrichtung) && $this->writeChild($kind, $werte, $ausrichtung)) {
                ++$geaendert;
            }
        }

        return $geaendert;
    }

    /**
     * Ausrichtung, so wie der Editor sie meldet — ohne Viewport-Ebene.
     *
     * @param array<string, mixed> $zelle
     *
     * @return array<string, string> Präfix => Richtung, leer heißt "keine Klasse"
     */
    private function alignment(array $zelle): array
    {
        $werte = [];

        foreach ((array) $this->gridClasses->getPositioning() as $praefix) {
            if (!\array_key_exists((string) $praefix, $zelle)) {
                continue;
            }

            $richtung = (string) $zelle[$praefix];
            $werte[(string) $praefix] = \in_array($richtung, (array) $this->gridClasses->getDirections(), true) ? $richtung : '';
        }

        return $werte;
    }

    /**
     * @param array<string, array<string, int|null>> $werte       Viewport => Achse => Wert
     * @param array<string, string>                  $ausrichtung Präfix => Richtung
     */
    private function writeChild(ContentModel $kind, array $werte, array $ausrichtung): bool
    {
        if (!$this->security->isGranted(ContaoCorePermissions::DC_PREFIX.'tl_content', new UpdateAction('tl_content', $kind->row()))) {
            return false;
        }

        $neu = [];

        foreach (self::FIELDS as $feld => $praefixe) {
            $klassen = $this->classFormatter->merge(
                StringUtil::deserialize($kind->$feld, true),
                $praefixe,
                $werte,
                // Die Ausrichtung hängt nicht am Viewport und liegt in den Optionen
                'grid_options' === $feld ? $ausrichtung : [],
            );

            $neu[$feld] = $klassen ? serialize($klassen) : null;
        }

        if ($neu['grid_columns'] === $kind->grid_columns && $neu['grid_options'] === $kind->grid_options) {
            return false;
        }

        // Ein Entwurf (tstamp 0) bleibt einer: Er hat noch keine Fassung, die
        // sich festhalten ließe, und ein Zeitstempel machte ihn zum fertigen
        // Datensatz, obwohl ihn niemand gespeichert hat
        $entwurf = !$kind->tstamp;

        if (!$entwurf) {
            $versions = new Versions('tl_content', (int) $kind->id);
            $versions->initialize();
        }

        $kind->grid_columns = $neu['grid_columns'];
        $kind->grid_options = $neu['grid_options'];

        if (!$entwurf) {
            $kind->tstamp = time();
        }

        $kind->save();

        if (!$entwurf) {
            $versions->create();
        }

        return true;
    }



    private function grenze(mixed $wert, int $max): int|null
    {
        // Alles unter eins heißt "automatisch", und dafür gibt es keine Klasse
        if (!is_numeric($wert) || (int) $wert < 1) {
            return null;
        }

        return min($max, (int) $wert);
    }
}
