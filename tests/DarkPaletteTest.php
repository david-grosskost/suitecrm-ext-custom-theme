<?php

declare(strict_types=1);

namespace SuiteCrmExt\CustomTheme\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SuiteCrmExt\CustomTheme\Colour;
use SuiteCrmExt\CustomTheme\DarkPalette;
use SuiteCrmExt\CustomTheme\Palette;

#[CoversClass(DarkPalette::class)]
final class DarkPaletteTest extends TestCase
{
    /** @return iterable<string, array{array<string, string>}> */
    public static function clients(): iterable
    {
        yield 'couleurs par défaut' => [[]];
        yield 'client orange' => [['primary' => '#ff6600', 'header_from' => '#cc5200', 'header_to' => '#ffa366']];
        yield 'client bleu marine' => [['primary' => '#1a237e', 'header_from' => '#0d1442', 'header_to' => '#3949ab']];
        yield 'client au fond teinté' => [['surface' => '#fdf2e9', 'rail' => '#fff7ed']];
    }

    /** @param array<string, string> $client */
    #[DataProvider('clients')]
    public function testTheDarkPageIsDark(array $client): void
    {
        $dark = DarkPalette::settings($client);

        self::assertLessThan(0.2, Colour::toHsl($dark['surface'])['l']);
        self::assertLessThan(0.2, Colour::toHsl($dark['rail'])['l']);
        self::assertNotSame($dark['surface'], $dark['rail'], 'le menu se détache du fond');
    }

    /** @param array<string, string> $client */
    #[DataProvider('clients')]
    public function testTheAccentsStandOutAgainstTheDarkPage(array $client): void
    {
        $dark = DarkPalette::settings($client);

        self::assertGreaterThanOrEqual(DarkPalette::ACCENT_CONTRAST, Colour::contrast($dark['primary'], $dark['surface']));
        self::assertGreaterThanOrEqual(DarkPalette::ACCENT_CONTRAST, Colour::contrast($dark['blue'], $dark['surface']));
    }

    /** @param array<string, string> $client */
    #[DataProvider('clients')]
    public function testWhiteTextReadsOnTheDarkHeader(array $client): void
    {
        $dark = DarkPalette::settings($client);

        self::assertGreaterThanOrEqual(4.5, Colour::contrast('#ffffff', $dark['header_from']));
        self::assertGreaterThanOrEqual(4.5, Colour::contrast('#ffffff', $dark['header_to']));
    }

    public function testKeepsTheHueOfTheClient(): void
    {
        $dark = DarkPalette::settings(['primary' => '#ff6600']);

        self::assertEqualsWithDelta(Colour::toHsl('#ff6600')['h'], Colour::toHsl($dark['primary'])['h'], 3.0);
    }

    public function testKeepsWhatTheAdministratorChoseForTheDarkMode(): void
    {
        $dark = DarkPalette::settings(['dark_surface' => '#0A0A0A', 'dark_primary' => '#123456']);

        self::assertSame('#0a0a0a', $dark['surface']);
        self::assertSame('#123456', $dark['primary'], 'un choix n\'est jamais corrigé');
    }

    public function testWorksTheAccentsOutAgainstTheChosenPage(): void
    {
        $dark = DarkPalette::settings(['primary' => '#1a237e', 'dark_surface' => '#000000']);

        self::assertGreaterThanOrEqual(DarkPalette::ACCENT_CONTRAST, Colour::contrast($dark['primary'], '#000000'));
    }

    public function testIgnoresADarkValueThatIsNotAColour(): void
    {
        self::assertSame(DarkPalette::settings([]), DarkPalette::settings(['dark_surface' => 'noir']));
    }

    public function testHasEveryColourOfTheLightPalette(): void
    {
        self::assertSame(array_keys(Palette::DEFAULTS), array_keys(DarkPalette::settings([])));
    }
}
