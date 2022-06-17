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

use ErdmannFreunde\ContaoGridBundle\GridClasses;

final class GridClassesOptionsListener
{
    private $gridClasses;

    public function __construct(GridClasses $gridClasses)
    {
        $this->gridClasses = $gridClasses;
    }

    public function onOptionsCallback(): array
    {
        return $this->gridClasses->getGridClassOptions();
    }
}
