<?php

declare(strict_types=1);

/*
 * This file is part of erdmannfreunde/contao-grid-bundle.
 *
 * (c) Erdmann & Freunde <https://erdmann-freunde.de>
 *
 * @license MIT
 */

use ErdmannFreunde\ContaoGridBundle\Form\FormColEnd;
use ErdmannFreunde\ContaoGridBundle\Form\FormColStart;
use ErdmannFreunde\ContaoGridBundle\Form\FormRowEnd;
use ErdmannFreunde\ContaoGridBundle\Form\FormRowStart;

$GLOBALS['TL_FFL']['rowStart'] = FormRowStart::class;
$GLOBALS['TL_FFL']['rowEnd'] = FormRowEnd::class;
$GLOBALS['TL_FFL']['colStart'] = FormColStart::class;
$GLOBALS['TL_FFL']['colEnd'] = FormColEnd::class;

$GLOBALS['TL_WRAPPERS']['start'][] = 'rowStart';
$GLOBALS['TL_WRAPPERS']['stop'][] = 'rowEnd';
$GLOBALS['TL_WRAPPERS']['start'][] = 'colStart';
$GLOBALS['TL_WRAPPERS']['stop'][] = 'colEnd';
$GLOBALS['TL_WRAPPERS']['start'][] = 'groupStart';
$GLOBALS['TL_WRAPPERS']['stop'][] = 'groupEnd';
