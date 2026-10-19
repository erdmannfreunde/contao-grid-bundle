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
use Contao\StringUtil;
use ErdmannFreunde\ContaoGridBundle\Grid\GridWriter;

/**
 * Gibt einem neuen Kind sofort die Breite, die das Muster vorsieht.
 *
 * Ohne das trüge ein frisch angelegtes Element erst dann Klassen, wenn jemand
 * das Grid noch einmal speichert — dazwischen stünde es im Frontend eine Spur
 * breit da, während der Editor es längst im Muster zeigt.
 *
 * Nur gleichmäßige Muster vergeben von sich aus eine Breite. Im freien Modus
 * gibt es kein Maß, das für ein unbekanntes Element gelten könnte.
 */
#[AsCallback(table: 'tl_content', target: 'config.oncreate')]
final readonly class GridChildDefaultsListener
{
    public function __construct(private GridWriter $gridWriter)
    {
    }

    /**
     * @param array<string, mixed> $set
     */
    public function __invoke(string $table, int|string $insertId, array $set, DataContainer $dc): void
    {
        if ('tl_content' !== ($set['ptable'] ?? null)) {
            return;
        }

        $pid = (int) ($set['pid'] ?? 0);

        if ($pid < 1) {
            return;
        }

        $grid = ContentModel::findById($pid);

        if ('grid' !== $grid?->type) {
            return;
        }

        $muster = StringUtil::deserialize($grid->grid_layout, true);

        foreach ($muster as $viewport => $wert) {
            if ('frei' === $wert) {
                unset($muster[$viewport]);
            }
        }

        if (!$muster) {
            return;
        }

        // Derselbe Weg wie beim Speichern des Grids: er lässt fremde Klassen
        // stehen und rührt die anderen Kinder nicht an, wenn sie schon passen
        $this->gridWriter->apply($pid, $muster, []);
    }
}
