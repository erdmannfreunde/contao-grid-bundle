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
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\StringUtil;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @Hook("getContentElement")
 */
final class AddGridClassesToContentListener
{

    public function __construct(private readonly RequestStack $requestStack, private readonly ScopeMatcher $scopeMatcher)
    {
    }

    public function __invoke(ContentModel $contentModel, string $strBuffer)
    {
        $strClasses = '';

        // Bei diesen ContentElementen soll nichts verändert werden
        $arrWrongCE = ['rowStart', 'rowEnd', 'colEnd'];

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
                $strBuffer = '<div class="tl_gray tl_content_right">'.$strClasses.'</div>'.$strBuffer;
            }
        }

        return $strBuffer;
    }
}
