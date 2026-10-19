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

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Contao\Input;
use Contao\StringUtil;
use ErdmannFreunde\ContaoGridBundle\Grid\GridWriter;
use ErdmannFreunde\ContaoGridBundle\GridClasses;

/**
 * Überträgt die Aufteilung aus dem Elementformular in die Kinder.
 *
 * Der einzige Schreibweg: der Raster-Editor liegt im Formular des
 * Grid-Elements, das aus der Inhaltsliste im Overlay öffnet.
 */
#[AsCallback(table: 'tl_content', target: 'config.onsubmit', priority: -10)]
final readonly class GridEditorSubmitListener
{
    public function __construct(
        private GridWriter $gridWriter,
        private GridClasses $gridClasses,
    ) {
    }

    public function __invoke(DataContainer $dc): void
    {
        $datensatz = (array) $dc->getCurrentRecord();

        if ('grid' !== ($datensatz['type'] ?? null)) {
            return;
        }

        $muster = StringUtil::deserialize($datensatz['grid_layout'] ?? null, true);

        $this->gridWriter->apply((int) $dc->id, $muster, $this->postedCells());
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function postedCells(): array
    {
        // postRaw, nicht post: Input::post kodiert Anführungszeichen, danach ist
        // das JSON nicht mehr lesbar. Geprüft wird unten, Wert für Wert.
        $roh = Input::postRaw('euf_grid_cells');

        if (!$roh) {
            return [];
        }

        try {
            $daten = json_decode((string) $roh, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return [];
        }

        $zellen = [];

        foreach ((array) $daten as $id => $zelle) {
            $id = (int) $id;

            foreach ((array) (((array) $zelle)['viewports'] ?? []) as $viewport => $achsen) {
                if (!\in_array($viewport, $this->gridClasses->getViewports(), true)) {
                    continue;
                }

                foreach (['col', 'rowSpan', 'colStart', 'rowStart'] as $achse) {
                    $wert = ((array) $achsen)[$achse] ?? null;

                    if (is_numeric($wert)) {
                        $zellen[$id]['viewports'][(string) $viewport][$achse] = (int) $wert;
                    }
                }
            }

            // Die Ausrichtung hängt an der Zelle, nicht am Viewport. Ein leerer
            // Wert ist eine Aussage: die Klasse soll weg.
            foreach ((array) $this->gridClasses->getPositioning() as $praefix) {
                if (\array_key_exists((string) $praefix, (array) $zelle)) {
                    $zellen[$id][(string) $praefix] = (string) ((array) $zelle)[$praefix];
                }
            }
        }

        return $zellen;
    }
}
