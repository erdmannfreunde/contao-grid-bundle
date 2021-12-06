<?php

declare(strict_types=1);

/*
 * This file is part of erdmannfreunde/contao-grid-bundle.
 *
 * (c) Erdmann & Freunde <https://erdmann-freunde.de>
 *
 * @license MIT
 */

namespace ErdmannFreunde\ContaoGridBundle;

use ErdmannFreunde\ContaoGridBundle\DependencyInjection\ErdmannFreundeContaoGridExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class ErdmannFreundeContaoGridBundle extends Bundle
{
    public function getContainerExtension(): ErdmannFreundeContaoGridExtension
    {
        return new ErdmannFreundeContaoGridExtension();
    }
}
