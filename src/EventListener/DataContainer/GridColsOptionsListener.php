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

use Contao\CoreBundle\ServiceAnnotation\Callback;
use ErdmannFreunde\ContaoGridBundle\GridClasses;

/**
 * @Callback(table="tl_content", target="fields.grid_columns.options")
 * @Callback(table="tl_form_field", target="fields.grid_columns.options")
 */
final class GridColsOptionsListener
{
    private $gridClasses;

    public function __construct(GridClasses $gridClasses)
    {
        $this->gridClasses = $gridClasses;
    }

    public function __invoke(): array
    {
        return $this->gridClasses->getGridColumnOptions();
    }
}
