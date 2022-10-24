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
        if (!\in_array($dc->activeRecord->type, ['rowStart', 'colStart'], true)) {
            return;
        }

        if (
            'auto' !== Input::post('SUBMIT_TYPE') && $this->siblingStopElmentIsMissing(
                $dc->activeRecord->pid,
                $dc->activeRecord->ptable,
                $dc->activeRecord->sorting,
                substr($dc->activeRecord->type, 0, 3)
            )
        ) {
            $data = $dc->activeRecord->row();
            unset($data['id']);
            $data['type'] = str_replace('Start', 'End', $dc->activeRecord->type);
            ++$data['sorting'];

            $newElement = new ContentModel();

            $newElement->tstamp = time();
            $newElement->setRow($data);
            $newElement->save();
        }
    }

    private function siblingStopElmentIsMissing($pid, $ptable, $sorting, $rowOrCol): bool
    {
        if (!\in_array($rowOrCol, ['row', 'col'], true)) {
            throw new \InvalidArgumentException('Argument $rowOrCol must be either "row" or "col"');
        }

        $statement = Database::getInstance()
            ->prepare(
                sprintf('SELECT * FROM tl_content WHERE pid=? AND ptable=? AND sorting>? AND type IN("%sStart", "%sEnd") ORDER BY sorting', $rowOrCol, $rowOrCol)
            )
            ->limit(1)
            ->execute($pid, $ptable, $sorting)
        ;

        if (false === $row = $statement->fetchAssoc()) {
            return true;
        }

        if ($rowOrCol.'End' !== $row['type']) {
            return true;
        }

        return false;
    }
}
