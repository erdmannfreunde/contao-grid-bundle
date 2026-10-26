<?php

declare(strict_types=1);

/*
 * This file is part of erdmannfreunde/contao-grid-bundle.
 *
 * (c) Erdmann & Freunde <https://erdmann-freunde.de>
 *
 * @license MIT
 */

namespace ErdmannFreunde\ContaoGridBundle\EventListener;

use Contao\ContentModel;
use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\StringUtil;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsHook('getContentElement')]
final readonly class AddGridClassesToContentListener
{
    public function __construct(
        private RequestStack $requestStack,
        private ScopeMatcher $scopeMatcher,
    ) {
    }

    public function __invoke(ContentModel $contentModel, string $strBuffer)
    {
        $strClasses = '';

        // Bei diesen ContentElementen soll nichts verändert werden
        $arrWrongCE = ['rowStart', 'rowEnd', 'colEnd'];

        // Kinder eines Grids bekommen ihr Zell-div vom Grid-Template, nicht von
        // hier. Die Notiz mit den Klassen gehört im Backend trotzdem an die Karte.
        if ('tl_content' === $contentModel->ptable && $this->isGridChild((int) $contentModel->pid)) {
            $klassen = $this->gridClasses($contentModel);

            if ($klassen && $this->isBackend()) {
                return '<div class="tl_grid_note">'.$klassen.'</div>'.$strBuffer;
            }

            return $strBuffer;
        }

        if (
            !\in_array($contentModel->type, $arrWrongCE, true)
            && (isset($contentModel->grid_columns) || isset($contentModel->grid_options))
        ) {
            $strClasses = $this->gridClasses($contentModel);

            switch ($contentModel->type) {
                case 'rowStart':
                case 'rowEnd':
                case 'colEnd':
                    // code...
                    break;

                case 'colStart':
                    $strBuffer = preg_replace('/(?<=["\s])ce_colStart(?=["\s])/', 'ce_colStart '.$strClasses, $strBuffer, 1);
                    break;

                default:
                    $strBuffer = '<div class="'.$strClasses.'">'.$strBuffer.'</div>';
                    break;
            }

            if ($this->isBackend()) {
                $strBuffer = '<div class="tl_grid_note">'.$strClasses.'</div>'.$strBuffer;
            }
        }

        return $strBuffer;
    }

    /**
     * Die Klassen beider Felder, durch Leerzeichen getrennt.
     */
    private function gridClasses(ContentModel $contentModel): string
    {
        $klassen = [];

        foreach (['grid_columns', 'grid_options'] as $feld) {
            foreach (StringUtil::deserialize($contentModel->$feld, true) as $klasse) {
                if ($klasse) {
                    $klassen[] = (string) $klasse;
                }
            }
        }

        return $klassen ? implode(' ', $klassen).' ' : '';
    }

    private function isBackend(): bool
    {
        return $this->scopeMatcher->isBackendRequest($this->requestStack->getCurrentRequest());
    }

    private function isGridChild(int $pid): bool
    {
        // Contaos Model-Registry cached den Datensatz pro Request
        $parent = ContentModel::findById($pid);

        return \in_array($parent?->type, ['grid', 'grid_cell'], true);
    }
}
