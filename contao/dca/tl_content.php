<?php

declare(strict_types=1);

/*
 * This file is part of erdmannfreunde/contao-grid-bundle.
 *
 * (c) Erdmann & Freunde <https://erdmann-freunde.de>
 *
 * @license MIT
 */

$GLOBALS['TL_DCA']['tl_content']['palettes']['rowStart'] = '{type_legend},type;{expert_legend:hide},cssID';
$GLOBALS['TL_DCA']['tl_content']['palettes']['rowEnd'] = '{type_legend},type';
$GLOBALS['TL_DCA']['tl_content']['palettes']['colStart'] =
    '{type_legend},type;{grid_legend},grid_columns,grid_options;{expert_legend:hide},cssID';
$GLOBALS['TL_DCA']['tl_content']['palettes']['colEnd'] = '{type_legend},type';
$GLOBALS['TL_DCA']['tl_content']['palettes']['groupStart'] = '{type_legend},type, group_tag;{expert_legend:hide},cssID';
$GLOBALS['TL_DCA']['tl_content']['palettes']['groupEnd'] = '{type_legend},type';

$GLOBALS['TL_DCA']['tl_content']['fields']['grid_columns'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['grid_columns'],
    'exclude' => true,
    'search' => true,
    'inputType' => 'select',
    'eval' => [
        'mandatory' => false,
        'multiple' => true,
        'size' => 10,
        'tl_class' => 'w50 w50h autoheight',
        'chosen' => true,
    ],
    'sql' => 'text NULL',
];

$GLOBALS['TL_DCA']['tl_content']['fields']['grid_options'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['grid_options'],
    'exclude' => true,
    'search' => true,
    'inputType' => 'select',
    'eval' => [
        'mandatory' => false,
        'multiple' => true,
        'size' => 10,
        'tl_class' => 'w50 w50h autoheight',
        'chosen' => true,
    ],
    'sql' => 'text NULL',
];

$GLOBALS['TL_DCA']['tl_content']['fields']['group_tag'] = [
    'exclude' => true,
    'search' => true,
    'inputType' => 'select',
    'eval' => [
        'mandatory' => false,
        'size' => 10,
        'tl_class' => 'clr w50 w50h autoheight',
    ],
    'sql' => 'text NULL',
];
