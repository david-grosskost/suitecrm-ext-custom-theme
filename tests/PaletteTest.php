<?php

declare(strict_types=1);

namespace SuiteCrmExt\CustomTheme\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SuiteCrmExt\CustomTheme\Palette;

#[CoversClass(Palette::class)]
final class PaletteTest extends TestCase
{
    public function testUsesTheDefaultsWhenNothingWasChosen(): void
    {
        self::assertSame(Palette::DEFAULTS, Palette::settings([]));
    }

    public function testKeepsWhatTheAdministratorChose(): void
    {
        $settings = Palette::settings(['primary' => '#AA3366']);

        self::assertSame('#aa3366', $settings['primary'], 'la casse est normalisée');
        self::assertSame(Palette::DEFAULTS['surface'], $settings['surface'], 'le reste garde sa valeur');
    }

    public function testIgnoresAValueThatIsNotAColour(): void
    {
        foreach (['rouge', '#abc', '#12345g', '', null, 42, ['#ffffff']] as $wrong) {
            $settings = Palette::settings(['primary' => $wrong]);

            self::assertSame(Palette::DEFAULTS['primary'], $settings['primary']);
        }
    }

    public function testGivesAModuleAlwaysTheSameAccent(): void
    {
        self::assertSame(Palette::accentFor('Accounts'), Palette::accentFor('Accounts'));
        self::assertSame(Palette::accentFor('Accounts'), Palette::accentFor('accounts'));
    }

    public function testGivesAnAccentTakenFromTheList(): void
    {
        foreach (['Accounts', 'Contacts', 'Opportunities', 'AOS_Quotes', 'Module1'] as $module) {
            self::assertContains(Palette::accentFor($module), Palette::ACCENTS);
        }
    }

    public function testGivesEveryRankItsOwnAccentUntilTheListRunsOut(): void
    {
        $count = count(Palette::ACCENTS);
        $used = array_map(Palette::accentAt(...), range(0, $count - 1));

        self::assertSame(Palette::ACCENTS, $used, 'les premiers modules ont tous une couleur différente');
        self::assertSame(Palette::accentAt(0), Palette::accentAt($count), 'au-delà, la liste recommence');
    }

    public function testTakesANegativeRank(): void
    {
        // crc32 dépasse l'entier signé sur certaines plateformes.
        self::assertContains(Palette::accentAt(-7), Palette::ACCENTS);
    }

    public function testBuildsTheVariablesSuiteCrmReads(): void
    {
        $variables = Palette::variables(['primary' => '#123456']);

        self::assertSame('#123456', $variables['--primary'], 'la variable de SuiteCRM 8');
        self::assertSame('#123456', $variables['--primary-color'], 'celle de PrimeNG');
        self::assertArrayHasKey('--st-header-from', $variables);
        foreach ($variables as $name => $value) {
            self::assertStringStartsWith('--', $name);
            self::assertTrue(Palette::isColour($value), $name . ' doit être une couleur');
        }
    }
}
