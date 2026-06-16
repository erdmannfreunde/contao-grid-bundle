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
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCallback(table: 'tl_content', target: 'fields.grid_columns.options')]
#[AsCallback(table: 'tl_form_field', target: 'fields.grid_columns.options')]
final readonly class GridColsOptionsListener
{
    public function __construct(
        private GridClasses $gridClasses,
        private TranslatorInterface $translator,
        private bool $translatedLabels = false,
    ) {
    }

    public function __invoke(): array
    {
        $options = $this->gridClasses->getGridColumnOptions();

        if (!$this->translatedLabels) {
            return $options;
        }

        $translated = [];

        foreach ($options as $group => $values) {
            $groupLabel = $this->translateOrFallback('group.'.$group, $group);

            foreach ($values as $value) {
                $translated[$groupLabel][$value] = $this->translateOrFallback('column.'.$value, $value);
            }
        }

        return $translated;
    }

    private function translateOrFallback(string $id, string $fallback): string
    {
        $translated = $this->translator->trans($id, domain: 'grid');

        return $translated === $id ? $fallback : $translated;
    }
}
