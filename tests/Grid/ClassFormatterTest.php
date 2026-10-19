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

use ErdmannFreunde\ContaoGridBundle\Grid\ClassFormatter;
use ErdmannFreunde\ContaoGridBundle\Tests\GridClassesFixture;
use PHPUnit\Framework\TestCase;

class ClassFormatterTest extends TestCase
{
    use GridClassesFixture;

    private const WIDTHS = ['col', 'row'];

    private const STARTS = ['col-start', 'row-start'];

    public function testWritesTheWidthOfAViewport(): void
    {
        $klassen = $this->formatter()->merge([], self::WIDTHS, ['md' => ['col' => 6]]);

        $this->assertSame(['col-md-6'], $klassen);
    }

    public function testReplacesTheWidthItManages(): void
    {
        $klassen = $this->formatter()->merge(['col-md-4'], self::WIDTHS, ['md' => ['col' => 6]]);

        $this->assertSame(['col-md-6'], $klassen);
    }

    public function testKeepsForeignClasses(): void
    {
        $klassen = $this->formatter()->merge(
            ['col-md-4', 'offset-md-2', 'my-own-class'],
            self::WIDTHS,
            ['md' => ['col' => 6]],
        );

        $this->assertSame(['col-md-6', 'offset-md-2', 'my-own-class'], $klassen);
    }

    public function testLeavesViewportsItWasNotToldAbout(): void
    {
        $klassen = $this->formatter()->merge(['col-md-4', 'col-xl-3'], self::WIDTHS, ['md' => ['col' => 6]]);

        $this->assertSame(['col-md-6', 'col-xl-3'], $klassen, 'xl kam im Editor nicht vor und bleibt stehen');
    }

    public function testDoesNotWriteTheDefaultHeight(): void
    {
        $klassen = $this->formatter()->merge([], self::WIDTHS, ['md' => ['col' => 6, 'row' => 1]]);

        $this->assertSame(['col-md-6'], $klassen, 'eine Zeile hoch ist der Normalfall');
    }

    public function testWritesAHeightOfTwoRows(): void
    {
        $klassen = $this->formatter()->merge([], self::WIDTHS, ['md' => ['col' => 6, 'row' => 2]]);

        $this->assertSame(['col-md-6', 'row-md-2'], $klassen);
    }

    public function testNullRemovesTheClassOfThatAxis(): void
    {
        $klassen = $this->formatter()->merge(
            ['col-md-4', 'row-md-2'],
            self::WIDTHS,
            ['md' => ['col' => 6, 'row' => null]],
        );

        $this->assertSame(['col-md-6'], $klassen, 'ein Muster räumt die Höhe weg');
    }

    public function testAPatternClearsTheStartValuesOfItsViewport(): void
    {
        $klassen = $this->formatter()->merge(
            ['col-start-md-7', 'row-start-md-2'],
            self::STARTS,
            ['md' => ['col' => 6, 'row' => null, 'col-start' => null, 'row-start' => null]],
        );

        $this->assertSame([], $klassen);
    }

    public function testFreeModeLeavesTheStartRowAloneWhenItHearsNothing(): void
    {
        $klassen = $this->formatter()->merge(
            ['row-start-md-2'],
            self::STARTS,
            ['md' => ['col' => 6, 'row' => 1, 'col-start' => 3]],
        );

        $this->assertSame(['col-start-md-3', 'row-start-md-2'], $klassen);
    }

    public function testDoesNotTouchTheStartRowWhenWritingTheHeight(): void
    {
        $klassen = $this->formatter()->merge(['row-start-md-4'], self::WIDTHS, ['md' => ['row' => 2]]);

        $this->assertSame(['row-md-2', 'row-start-md-4'], $klassen);
    }

    public function testTellsColumnAndStartApart(): void
    {
        $klassen = $this->formatter()->merge(['col-start-md-7'], self::WIDTHS, ['md' => ['col' => 6]]);

        $this->assertSame(['col-md-6', 'col-start-md-7'], $klassen, 'col-start gehört nicht in dieses Feld');
    }

    public function testWritesAlignmentWithoutViewport(): void
    {
        $klassen = $this->formatter()->merge([], self::STARTS, [], ['align' => 'center', 'justify' => '']);

        $this->assertSame(['align-center'], $klassen);
    }

    public function testAnEmptyDirectionRemovesTheAlignment(): void
    {
        $klassen = $this->formatter()->merge(['align-center'], self::STARTS, [], ['align' => '']);

        $this->assertSame([], $klassen);
    }

    public function testKeepsAlignmentNobodyAskedAbout(): void
    {
        $klassen = $this->formatter()->merge(['align-center', 'justify-md-end'], self::STARTS, [], []);

        $this->assertSame(['align-center', 'justify-md-end'], $klassen);
    }

    public function testWritesNothingWhenItHearsNothing(): void
    {
        $klassen = $this->formatter()->merge(['col-md-4'], self::WIDTHS, []);

        $this->assertSame(['col-md-4'], $klassen);
    }

    private function formatter(): ClassFormatter
    {
        return new ClassFormatter($this->gridClasses());
    }
}
