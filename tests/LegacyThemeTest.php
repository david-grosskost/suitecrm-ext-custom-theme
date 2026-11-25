<?php

declare(strict_types=1);

namespace SuiteCrmExt\CustomTheme\Tests;

use PHPUnit\Framework\TestCase;
use SuiteCrmExt\CustomTheme\LegacyTheme;

/**
 * The stylesheet this extension builds for the screens SuiteCRM draws
 * itself, checked on a piece of CSS shaped like Dawn's.
 */
final class LegacyThemeTest extends TestCase
{
    private const string DAWN = <<<'CSS'
        .dashlet .hd{background-color:#534d64;color:#fff}
        thead th{background:#93A4B3;border-bottom:1px solid #93a4b3}
        .btn-primary{background-color:#f08377}
        .untouched{background:#f5f5f5;color:#212529}
        CSS;

    public function testHandsEveryChosenColourToItsVariable(): void
    {
        $painted = LegacyTheme::paint(self::DAWN, []);

        foreach (LegacyTheme::COLOURS as $colour => $name) {
            if (stripos(self::DAWN, $colour) === false) {
                continue;
            }
            self::assertStringContainsString("var($name, $colour)", $painted);
        }
    }

    public function testKeepsTheOriginalColourAsTheFallback(): void
    {
        $painted = LegacyTheme::paint(self::DAWN, []);

        // A variable that never arrives must leave the screen as SuiteCRM
        // drew it, so every replacement carries the colour it replaced.
        self::assertStringContainsString('var(--ct-panel-head, #534d64)', $painted);
        self::assertSame(0, preg_match('/var\(--ct-[a-z-]+\)/', $painted), 'no variable without a fallback');
    }

    public function testMatchesWhateverTheCaseOfTheColour(): void
    {
        // Dawn writes the same colour both ways.
        $painted = LegacyTheme::paint(self::DAWN, []);

        self::assertStringNotContainsString('#93A4B3;', $painted);
        self::assertSame(2, substr_count($painted, 'var(--ct-table-head,'));
    }

    public function testLeavesTheGreysAndTheTextAlone(): void
    {
        $painted = LegacyTheme::paint(self::DAWN, []);

        self::assertStringContainsString('.untouched{background:#f5f5f5;color:#212529}', $painted);
    }

    public function testLeavesTheCharsetFirst(): void
    {
        // A stylesheet's @charset is ignored unless it comes first, so the
        // block of variables goes after it.
        $painted = LegacyTheme::paint("@charset \"UTF-8\";\n" . self::DAWN, []);

        self::assertStringStartsWith('@charset "UTF-8";', $painted);
        self::assertStringContainsString(':root{', $painted);
    }

    public function testDeclaresTheVariablesItself(): void
    {
        // The legacy screens are an iframe: variables set outside never reach
        // them, so the stylesheet carries its own.
        $painted = LegacyTheme::paint(self::DAWN, ['primary' => '#112233', 'header_from' => '#445566']);
        $values = LegacyTheme::values(['primary' => '#112233', 'header_from' => '#445566']);

        self::assertStringContainsString('/* ' . LegacyTheme::NAME . " */\n:root{", $painted);
        foreach ($values as $name => $colour) {
            self::assertStringContainsString("$name:$colour;", $painted);
        }
    }

    public function testEveryColourHasAValue(): void
    {
        $values = LegacyTheme::values([]);

        self::assertSame(
            array_values(LegacyTheme::COLOURS),
            array_keys($values),
            'each colour handed over is driven by a variable that has a value',
        );
        foreach ($values as $name => $colour) {
            self::assertMatchesRegularExpression('/^#[0-9a-f]{6}$/i', $colour, "$name is a colour");
        }
    }

    public function testRefusesAStylesheetItWouldBreak(): void
    {
        $safe = self::DAWN;
        $broken = $safe . "\n.icon{background:url(\"data:image/svg+xml,%3Csvg fill='#534d64'%3E\")}";

        self::assertTrue(LegacyTheme::isPaintable($safe));
        self::assertFalse(LegacyTheme::isPaintable($broken), 'a colour inside a url() cannot become a variable');
    }

    public function testTheSourceAndTheTargetAreWhereSuiteCrmLooks(): void
    {
        self::assertSame('themes/suite8/css/Dawn/style.css', LegacyTheme::SOURCE);
        self::assertSame('custom/themes/CustomTheme/css/Dawn/style.css', LegacyTheme::TARGET);
        self::assertFileExists(__DIR__ . '/../legacy/themes/' . LegacyTheme::NAME . '/themedef.php');
    }
}
