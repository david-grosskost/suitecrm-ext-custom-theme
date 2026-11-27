<?php

declare(strict_types=1);

namespace SuiteCrmExt\CustomTheme\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SuiteCrmExt\CustomTheme\Colour;

#[CoversClass(Colour::class)]
final class ColourTest extends TestCase
{
    /** @return iterable<string, array{string}> */
    public static function colours(): iterable
    {
        yield 'blanc' => ['#ffffff'];
        yield 'noir' => ['#000000'];
        yield 'gris' => ['#93a4b3'];
        yield 'bleu du thème' => ['#0284c5'];
        yield 'corail de SuiteCRM' => ['#f08377'];
        yield 'violet' => ['#8d74cc'];
    }

    #[DataProvider('colours')]
    public function testGivesBackTheSameColourThroughHueSaturationAndLightness(string $colour): void
    {
        $hsl = Colour::toHsl($colour);

        self::assertSame($colour, Colour::fromHsl($hsl['h'], $hsl['s'], $hsl['l']));
    }

    public function testMeasuresTheContrastsOfReference(): void
    {
        self::assertEqualsWithDelta(21.0, Colour::contrast('#000000', '#ffffff'), 0.01);
        self::assertEqualsWithDelta(1.0, Colour::contrast('#777777', '#777777'), 0.01);
        self::assertEqualsWithDelta(4.48, Colour::contrast('#777777', '#ffffff'), 0.01);
        self::assertSame(Colour::contrast('#0284c5', '#ffffff'), Colour::contrast('#ffffff', '#0284c5'), 'dans un sens comme dans l\'autre');
    }

    public function testChangesOnlyTheLightness(): void
    {
        $darker = Colour::toHsl(Colour::withLightness('#0284c5', 0.2));
        $original = Colour::toHsl('#0284c5');

        self::assertEqualsWithDelta(0.2, $darker['l'], 0.01);
        self::assertEqualsWithDelta($original['h'], $darker['h'], 2.0);
    }

    public function testLightensAColourUntilItStandsOutAgainstADarkPage(): void
    {
        $lightened = Colour::lightenedAgainst('#1a237e', '#151c23', 3.0);

        self::assertGreaterThanOrEqual(3.0, Colour::contrast($lightened, '#151c23'));
        self::assertEqualsWithDelta(Colour::toHsl('#1a237e')['h'], Colour::toHsl($lightened)['h'], 3.0, 'la teinte est gardée');
    }

    public function testLeavesAColourThatAlreadyStandsOut(): void
    {
        self::assertSame('#f08377', Colour::lightenedAgainst('#F08377', '#151c23', 3.0));
    }
}
