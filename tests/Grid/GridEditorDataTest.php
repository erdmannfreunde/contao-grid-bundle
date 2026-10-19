<?php

declare(strict_types=1);

/*
 * This file is part of erdmannfreunde/contao-grid-bundle.
 *
 * (c) Erdmann & Freunde <https://erdmann-freunde.de>
 *
 * @license MIT
 */

namespace ErdmannFreunde\ContaoGridBundle\Tests\Grid;

use ErdmannFreunde\ContaoGridBundle\Grid\GridEditorData;
use ErdmannFreunde\ContaoGridBundle\Grid\SpanParser;
use ErdmannFreunde\ContaoGridBundle\Tests\GridClassesFixture;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Translator;

class GridEditorDataTest extends TestCase
{
    use GridClassesFixture;

    public function testOffersOnlyPatternsThatDivideTheColumns(): void
    {
        $this->assertSame(['1', '2', '3', '4', '6', 'frei'], $this->editorData()->patterns());
    }

    public function testFollowsAnotherColumnCount(): void
    {
        $zehn = $this->editorData(range(1, 10));

        $this->assertSame(['1', '2', '5', 'frei'], $zehn->patterns());
    }

    public function testAlwaysOffersTheWayOut(): void
    {
        $sieben = $this->editorData(range(1, 7));

        $this->assertSame(['1', 'frei'], $sieben->patterns(), 'eine Primzahl lässt nur eine Aufteilung zu');
    }

    public function testLabelsAViewportAfterItsKey(): void
    {
        $viewports = $this->editorData()->build(0, 'feld')['viewports'];

        $this->assertSame(['key' => 'md', 'label' => 'ab Screen MD'], $viewports[2]);
    }

    public function testAnOwnTranslationWins(): void
    {
        $daten = $this->editorData(null, ['viewport.md' => 'ab 768 px'])->build(0, 'feld');

        $this->assertSame('ab 768 px', $daten['viewports'][2]['label']);
    }

    /**
     * @param array<int>|null     $columns
     * @param array<string,string> $uebersetzungen
     */
    private function editorData(array|null $columns = null, array $uebersetzungen = []): GridEditorData
    {
        $gridClasses = $this->gridClasses(null, $columns);

        $translator = new Translator('de');
        $translator->addLoader('array', new ArrayLoader());
        $translator->addResource('array', ['viewport.default' => 'ab Screen %viewport%'] + $uebersetzungen, 'de', 'grid');

        return new GridEditorData($gridClasses, new SpanParser($gridClasses), $translator);
    }
}
