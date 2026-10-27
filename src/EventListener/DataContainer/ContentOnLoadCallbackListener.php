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
use Contao\CoreBundle\Routing\ScopeMatcher;

/**
 * Das Stylesheet für die Klassennotiz an den Karten.
 *
 * Die Notiz hängt an jedem Element mit Grid-Klassen, auch außerhalb eines
 * Grids — deshalb gehört das Stylesheet an jede Inhaltsliste. Das DCA lässt
 * sich aber auch im Frontend laden; ohne die Prüfung landete eine
 * Backend-Datei in der Seitenausgabe.
 */
#[AsCallback(table: 'tl_content', target: 'config.onload')]
final readonly class ContentOnLoadCallbackListener
{
    public function __construct(private ScopeMatcher $scopeMatcher)
    {
    }

    public function __invoke(): void
    {
        // Ohne Argument fragt der ScopeMatcher den Request-Stack selbst und
        // antwortet auf der Kommandozeile mit nein
        if (!$this->scopeMatcher->isBackendRequest()) {
            return;
        }

        $GLOBALS['TL_CSS'][] = 'bundles/erdmannfreundecontaogrid/grid_backend.css';
    }
}
