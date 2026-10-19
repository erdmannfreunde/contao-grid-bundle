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

        // Kinder eines Grids bekommen ihr Zell-div vom Grid-Template, nicht von hier
        if ('tl_content' === $contentModel->ptable && $this->isGridChild((int) $contentModel->pid)) {
            return $strBuffer;
        }

        if (
            !\in_array($contentModel->type, $arrWrongCE, true)
            && (isset($contentModel->grid_columns) || isset($contentModel->grid_options))
        ) {
            if ($contentModel->grid_columns) {
                $arrGridClasses = StringUtil::deserialize($contentModel->grid_columns);

                foreach ($arrGridClasses as $class) {
                    $strClasses .= $class.' ';
                }
            }

            if ($contentModel->grid_options) {
                $arrGridClasses = StringUtil::deserialize($contentModel->grid_options);

                foreach ($arrGridClasses as $class) {
                    $strClasses .= $class.' ';
                }
            }

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

            if ($this->scopeMatcher->isBackendRequest($this->requestStack->getCurrentRequest())) {
                $strBuffer = '<div class="tl_grid_note">'.$strClasses.'</div>'.$strBuffer;
            }
        }

        return $strBuffer;
    }

    private function isGridChild(int $pid): bool
    {
        // Contaos Model-Registry cached den Datensatz pro Request
        $parent = ContentModel::findById($pid);

        return \in_array($parent?->type, ['grid', 'grid_cell'], true);
    }
}
