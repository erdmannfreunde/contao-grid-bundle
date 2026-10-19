<?php

declare(strict_types=1);

/*
 * This file is part of erdmannfreunde/contao-grid-bundle.
 *
 * (c) Erdmann & Freunde <https://erdmann-freunde.de>
 *
 * @license MIT
 */

namespace ErdmannFreunde\ContaoGridBundle\Security\Voter;

use Contao\CoreBundle\Security\DataContainer\CreateAction;
use Contao\CoreBundle\Security\DataContainer\DeleteAction;
use Contao\CoreBundle\Security\DataContainer\ReadAction;
use Contao\CoreBundle\Security\DataContainer\UpdateAction;
use Contao\CoreBundle\Security\Voter\DataContainer\AbstractDataContainerVoter;
use Doctrine\DBAL\Connection;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Contracts\Service\ResetInterface;

/**
 * Hält Grids aus Grids heraus.
 *
 * Ein Grid nimmt jeden Elementtyp auf — nur sich selbst nicht: Ein
 * verschachteltes Grid bekäme seine Breite vom Editor des Eltern-Grids, hätte
 * aber daneben seinen eigenen Editor für die Kinder. Zwei Raster in einem
 * Formular sind kaum auseinanderzuhalten. Eine Spalte in einer Spalte ist aus
 * demselben Grund keine gute Idee: Sie stünde ohne Raster da.
 *
 * Der Weg über die Rechteprüfung statt über allowedTypes hat zwei Gründe: Die
 * Typ-Auswahl fragt ohnehin hier nach, und er greift auch beim Verschieben und
 * beim Einfügen aus der Zwischenablage.
 */
final class GridNestingVoter extends AbstractDataContainerVoter implements ResetInterface
{
    private const FORBIDDEN = [
        'grid' => ['grid'],
        'grid_cell' => ['grid', 'grid_cell'],
    ];

    /**
     * @var array<int, string>
     */
    private array $parentTypes = [];

    public function __construct(private readonly Connection $connection)
    {
    }

    public function reset(): void
    {
        $this->parentTypes = [];
    }

    protected function getTable(): string
    {
        return 'tl_content';
    }

    protected function hasAccess(TokenInterface $token, CreateAction|DeleteAction|ReadAction|UpdateAction $action): bool
    {
        // Was einmal angelegt wurde, bleibt lesbar und löschbar
        if ($action instanceof ReadAction || $action instanceof DeleteAction) {
            return true;
        }

        $neu = $action->getNew();
        $aktuell = $action instanceof UpdateAction ? $action->getCurrent() : [];

        if ('tl_content' !== ($neu['ptable'] ?? $aktuell['ptable'] ?? null)) {
            return true;
        }

        $pid = (int) $action->getNewPid();
        $typ = (string) ($neu['type'] ?? $aktuell['type'] ?? '');

        if ($pid < 1 || '' === $typ) {
            return true;
        }

        return !\in_array($typ, self::FORBIDDEN[$this->parentType($pid)] ?? [], true);
    }

    private function parentType(int $pid): string
    {
        // Die Typ-Auswahl fragt je Elementtyp einmal nach, das wäre sonst eine
        // Abfrage pro Zeile der Liste
        return $this->parentTypes[$pid] ??= (string) $this->connection->fetchOne('SELECT type FROM tl_content WHERE id = ?', [$pid]);
    }
}
