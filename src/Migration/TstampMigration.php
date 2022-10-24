<?php

declare(strict_types=1);

/*
 * This file is part of erdmannfreunde/contao-grid-bundle.
 *
 * (c) Erdmann & Freunde <https://erdmann-freunde.de>
 *
 * @license MIT
 */

namespace ErdmannFreunde\ContaoGridBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

class TstampMigration extends AbstractMigration
{

    public function __construct(private readonly Connection $connection)
    {
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->getSchemaManager();

        if (!$schemaManager->tablesExist(['tl_content'])) {
            return false;
        }

        $result = $this->connection
            ->executeQuery("SELECT id FROM tl_content WHERE tstamp = 0 AND (type='colEnd' OR type='rowEnd')")
            ->fetchNumeric()
        ;

        return false !== $result;
    }

    public function run(): MigrationResult
    {
        $this->connection->executeQuery(
            "UPDATE tl_content SET tstamp = :time WHERE tstamp = 0 AND (type='colEnd' OR type='rowEnd')",
            ['time' => time()]
        )
        ;

        return $this->createResult(
            true,
            'Restored 4.9.15 compatibility for Contao-Grid-Bundle'
        );
    }
}
