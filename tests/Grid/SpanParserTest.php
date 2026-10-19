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

use ErdmannFreunde\ContaoGridBundle\Grid\SpanParser;
use ErdmannFreunde\ContaoGridBundle\Tests\GridClassesFixture;
use PHPUnit\Framework\TestCase;

class SpanParserTest extends TestCase
{
    use GridClassesFixture;

    public function testReadsEachAxisFromTheFieldItLivesIn(): void
    {
        $geometrie = $this->parser()->parse(
            serialize(['col-md-6', 'row-md-2']),
            serialize(['col-start-md-2', 'row-start-lg-3']),
        );

        $this->assertSame(['md' => 6], $geometrie['col']);
        $this->assertSame(['md' => 2], $geometrie['row']);
        $this->assertSame(['md' => 2], $geometrie['col-start']);
        $this->assertSame(['lg' => 3], $geometrie['row-start']);
    }

    public function testKeepsColumnAndStartApart(): void
    {
        $geometrie = $this->parser()->parse(serialize(['col-md-4', 'col-start-md-7']), null);

        $this->assertSame(['md' => 4], $geometrie['col']);
        $this->assertSame(['md' => 7], $geometrie['col-start']);
    }

    public function testKeepsHeightAndStartRowApart(): void
    {
        $geometrie = $this->parser()->parse(serialize(['row-md-2']), serialize(['row-start-md-4']));

        $this->assertSame(['md' => 2], $geometrie['row']);
        $this->assertSame(['md' => 4], $geometrie['row-start'], 'row ist der Anfang von row-start');
    }

    public function testIgnoresForeignClasses(): void
    {
        $geometrie = $this->parser()->parse(serialize(['offset-md-2', 'my-own-class', 'col-xx-4']), null);

        $this->assertSame([], $geometrie['col']);
        $this->assertSame([], $geometrie['row']);
    }

    public function testReadsSeveralViewportsOfOneAxis(): void
    {
        $geometrie = $this->parser()->parse(serialize(['col-xs-12', 'col-md-6', 'col-xl-4']), null);

        $this->assertSame(['xs' => 12, 'md' => 6, 'xl' => 4], $geometrie['col']);
    }

    public function testCopesWithEmptyFields(): void
    {
        $geometrie = $this->parser()->parse(null, '');

        $this->assertSame(['col' => [], 'row' => [], 'col-start' => [], 'row-start' => []], $geometrie);
    }

    public function testInheritsTheNextSmallerValue(): void
    {
        $parser = $this->parser();
        $werte = ['md' => 6];

        $this->assertSame(12, $parser->effective($werte, 'xs', 12), 'unterhalb gilt der Standard');
        $this->assertSame(6, $parser->effective($werte, 'md', 12));
        $this->assertSame(6, $parser->effective($werte, 'xl', 12), 'oberhalb gilt der gesetzte Wert');
    }

    public function testTheNearerViewportWins(): void
    {
        $parser = $this->parser();

        $this->assertSame(4, $parser->effective(['xs' => 12, 'md' => 6, 'lg' => 4], 'xl', 12));
        $this->assertSame(6, $parser->effective(['xs' => 12, 'md' => 6, 'lg' => 4], 'md', 12));
    }

    public function testReadsAlignmentWithoutViewport(): void
    {
        $ausrichtung = $this->parser()->alignment(serialize(['align-center', 'justify-end']));

        $this->assertSame(['align' => 'center', 'justify' => 'end'], $ausrichtung);
    }

    public function testLeavesViewportBoundAlignmentAlone(): void
    {
        $ausrichtung = $this->parser()->alignment(serialize(['justify-md-end']));

        $this->assertSame(['align' => '', 'justify' => ''], $ausrichtung);
    }

    private function parser(): SpanParser
    {
        return new SpanParser($this->gridClasses());
    }
}
