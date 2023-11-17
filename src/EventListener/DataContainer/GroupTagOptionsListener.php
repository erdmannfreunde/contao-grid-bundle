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
 * @Callback(table="tl_content", target="fields.group_tag.options")
 */
final class GroupTagOptionsListener
{

    public function __construct(private readonly GridClasses $gridClasses)
    {
    }

    public function __invoke(): array
    {
        return $this->gridClasses->getGroupTagOptions();
    }
}
