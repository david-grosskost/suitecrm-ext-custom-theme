<?php

declare(strict_types=1);

namespace SuiteCrmExt\CustomTheme\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SuiteCrmExt\CustomTheme\Palette;
use SuiteCrmExt\CustomTheme\Theme;

#[CoversClass(Theme::class)]
final class ThemeTest extends TestCase
{
    public function testGivesAnAccentToEveryStandardModule(): void
    {
        $accents = Theme::payload([])['accents'];

        self::assertSame(Theme::STANDARD_MODULES, array_keys($accents));
        foreach ($accents as $module => $accent) {
            self::assertContains($accent, Palette::ACCENTS, $module);
        }
    }

    public function testGivesTheModulesSeenEveryDayDistinctAccents(): void
    {
        $accents = Theme::payload([])['accents'];
        $firstFew = array_slice($accents, 0, count(Palette::ACCENTS));

        self::assertSame(
            count($firstFew),
            count(array_unique($firstFew)),
            'deux modules côte à côte dans le rail ne doivent pas partager leur couleur',
        );
    }

    public function testSaysNothingOfTheInstance(): void
    {
        $payload = Theme::payload(['primary' => '#123456']);

        self::assertSame(['variables', 'accents', 'rail', 'legacy', 'angular'], array_keys($payload));
        self::assertSame(
            Theme::STANDARD_MODULES,
            array_keys($payload['accents']),
            'seuls les modules standard : cette entrée est ouverte sans connexion',
        );
    }

    public function testCarriesTheChosenColours(): void
    {
        $payload = Theme::payload(['primary' => '#ff0000']);

        self::assertSame('#ff0000', $payload['variables']['--primary']);
    }

    public function testGivesTheRailItsTwoWidths(): void
    {
        $rail = Theme::payload([])['rail'];

        self::assertSame(Theme::RAIL_WIDTH, $rail['width']);
        self::assertSame(Theme::RAIL_OPEN_WIDTH, $rail['open']);
        self::assertGreaterThan($rail['width'], $rail['open'], 'ouvert est plus large que replié');
    }

    public function testIsReadableJson(): void
    {
        $decoded = json_decode(Theme::json(['primary' => '#0a0b0c']), true, 512, JSON_THROW_ON_ERROR);

        self::assertIsArray($decoded);
        self::assertIsArray($decoded['variables']);
        self::assertSame('#0a0b0c', $decoded['variables']['--primary']);
    }
}
