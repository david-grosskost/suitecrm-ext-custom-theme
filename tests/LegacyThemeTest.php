<?php

declare(strict_types=1);

namespace SuiteCrmExt\CustomTheme\Tests;

use PHPUnit\Framework\TestCase;
use SuiteCrmExt\CustomTheme\LegacyTheme;

/**
 * The stylesheet this extension lays over SuiteCRM's, checked on a piece of
 * CSS shaped like Dawn's: minified, with an @media around some of it.
 */
final class LegacyThemeTest extends TestCase
{
    private const string DAWN = '.dashlet .hd{background-color:#534d64;color:#fff}'
        . 'thead th{background:#93A4B3;border-bottom:1px solid #93a4b3}'
        . '.untouched{background:#f5f5f5;color:#212529}'
        . '@media (min-width:768px){.btn{background-color:#f08377}.plain{margin:0}}'
        . '@media print{.plain{margin:0}}';

    public function testKeepsOnlyTheRulesThatCarryAColour(): void
    {
        $laid = LegacyTheme::paint(self::DAWN, []);

        self::assertStringContainsString('.dashlet .hd{', $laid);
        self::assertStringContainsString('thead th{', $laid);
        self::assertStringNotContainsString('.untouched', $laid, 'a rule without one of the six is dropped');
        self::assertStringNotContainsString('.plain', $laid);
    }

    public function testKeepsARuleInsideTheMediaItWasWrittenFor(): void
    {
        // Taken out of its @media, a rule meant for one screen width would
        // apply to every one.
        $laid = LegacyTheme::paint(self::DAWN, []);

        self::assertStringContainsString('@media (min-width:768px){.btn{', $laid);
        self::assertStringNotContainsString('@media print', $laid, 'a wrapper left empty is dropped whole');
    }

    public function testHandsEveryColourItKeepsToItsVariable(): void
    {
        $laid = LegacyTheme::paint(self::DAWN, []);

        self::assertStringContainsString('var(--ct-panel-head, #534d64)', $laid);
        self::assertStringContainsString('var(--ct-accent, #f08377)', $laid);
        self::assertSame(2, substr_count($laid, 'var(--ct-table-head,'), 'whatever the case it was written in');
        self::assertSame(0, preg_match('/var\(--ct-[a-z-]+\)/', $laid), 'no variable without its original as fallback');
    }

    public function testLeavesNoColourOfItsOwnBehind(): void
    {
        $laid = LegacyTheme::paint(self::DAWN, []);
        $rules = substr($laid, (int) strpos($laid, "}\n") + 2);

        foreach (array_keys(LegacyTheme::COLOURS) as $colour) {
            self::assertSame(
                0,
                preg_match('/(?<!, )' . preg_quote($colour, '/') . '/i', $rules),
                "$colour is only ever a fallback",
            );
        }
    }

    public function testDeclaresTheVariablesItself(): void
    {
        // The legacy screens are an iframe: variables set outside never reach
        // them, so the stylesheet carries its own.
        $chosen = ['primary' => '#112233', 'header_from' => '#445566'];
        $laid = LegacyTheme::paint(self::DAWN, $chosen);

        foreach (LegacyTheme::values($chosen) as $name => $colour) {
            self::assertStringContainsString("$name:$colour;", $laid);
        }
    }

    public function testEveryColourHandedOverHasAValue(): void
    {
        $values = LegacyTheme::values([]);

        self::assertSame(array_values(LegacyTheme::COLOURS), array_keys($values));
        foreach ($values as $name => $colour) {
            self::assertMatchesRegularExpression('/^#[0-9a-f]{6}$/i', $colour, "$name is a colour");
        }
    }

    public function testBalancesItsBraces(): void
    {
        $laid = LegacyTheme::paint(self::DAWN, []);

        self::assertSame(substr_count($laid, '{'), substr_count($laid, '}'));
    }

    public function testDropsTheCommentsItIsHandedOver(): void
    {
        $laid = LegacyTheme::paint("/*! Bootstrap */\n" . self::DAWN, []);

        self::assertStringNotContainsString('Bootstrap', $laid);
    }

    public function testRefusesAStylesheetItWouldBreak(): void
    {
        $broken = self::DAWN . ".icon{background:url(\"data:image/svg+xml,%3Csvg fill='#534d64'%3E\")}";

        self::assertTrue(LegacyTheme::isPaintable(self::DAWN));
        self::assertFalse(LegacyTheme::isPaintable($broken), 'a colour inside a url() cannot become a variable');
    }

    public function testLaysItselfOverSuiteCrmRatherThanReplacingIt(): void
    {
        // The instance stays on suite8: on any other theme SuiteCRM 8 serves
        // its old record views instead of the modern ones.
        self::assertSame('themes/suite8/css/Dawn/style.css', LegacyTheme::SOURCE);
        self::assertSame('custom/themes/suite8/css/Dawn/style.css', LegacyTheme::TARGET);
        self::assertSame('cache/themes/suite8/css/Dawn/style.css', LegacyTheme::CACHE);
    }
}
