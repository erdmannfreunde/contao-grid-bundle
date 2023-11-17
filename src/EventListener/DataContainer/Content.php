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

use Contao\ContentModel;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\Database;
use Contao\DataContainer;
use Contao\Input;

/**
 * @Callback(table="tl_content", target="config.onsubmit")
 */
final class Content
{
    public function __invoke(DataContainer $dc): void
    {
        $data = (array) $dc->getCurrentRecord();

        if (!\in_array($data['type'], ['rowStart', 'colStart', 'groupStart'], true)) {
            return;
        }

        if (
            'auto' !== Input::post('SUBMIT_TYPE') && $this->siblingStopElementIsMissing(
                $data['pid'],
                $data['ptable'],
                $data['sorting'],
                //substr($data['type'], 0, 3)
                str_replace('Start', '', $data['type'])
            )
        ) {

            unset($data['id']);
            $data['type'] = str_replace('Start', 'End', $data['type']);
            ++$data['sorting'];

            $newElement = new ContentModel();

            $newElement->tstamp = time();
            $newElement->setRow($data);
            $newElement->save();
        }
    }

    private function siblingStopElementIsMissing($pid, $ptable, $sorting, $rowOrColOrGroup): bool
    {
        if (!\in_array($rowOrColOrGroup, ['row', 'col', 'group'], true)) {
            throw new \InvalidArgumentException('Argument $rowOrColOrGroup must be either "row" or "col" or "group"');
        }

        $statement = Database::getInstance()
            ->prepare(
                sprintf('SELECT * FROM tl_content WHERE pid=? AND ptable=? AND sorting>? AND type IN("%sStart", "%sEnd") ORDER BY sorting', $rowOrColOrGroup, $rowOrColOrGroup)
            )
            ->limit(1)
            ->execute($pid, $ptable, $sorting)
        ;

        if (false === $row = $statement->fetchAssoc()) {
            return true;
        }

        if ($rowOrColOrGroup.'End' !== $row['type']) {
            return true;
        }

        return false;
    }
}
