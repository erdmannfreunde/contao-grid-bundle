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
use ErdmannFreunde\ContaoGridBundle\GridClasses;

#[AsCallback(table: 'tl_content', target: 'fields.grid_columns.options')]
#[AsCallback(table: 'tl_form_field', target: 'fields.grid_columns.options')]
final readonly class GridColsOptionsListener
{
    public function __construct(private GridClasses $gridClasses)
    {
    }

    public function __invoke(): array
    {
        return $this->gridClasses->getGridColumnOptions();
    }
}
