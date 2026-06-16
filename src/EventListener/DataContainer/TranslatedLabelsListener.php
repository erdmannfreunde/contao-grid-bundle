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

#[AsCallback(table: 'tl_content', target: 'config.onload')]
final readonly class TranslatedLabelsListener
{
    public function __construct(private bool $translatedLabels = false)
    {
    }

    public function __invoke(): void
    {
        // Kept for backwards compatibility with existing service wiring.
        // Label translation is handled directly in the options callbacks.
    }
}
