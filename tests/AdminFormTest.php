<?php

declare(strict_types=1);

namespace SuiteCrmExt\CustomTheme\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SuiteCrmExt\CustomTheme\AdminForm;
use SuiteCrmExt\CustomTheme\DarkPalette;
use SuiteCrmExt\CustomTheme\Page;
use SuiteCrmExt\CustomTheme\Palette;

#[CoversClass(AdminForm::class)]
final class AdminFormTest extends TestCase
{
    public function testKeepsTheColoursThatWereChosen(): void
    {
        $values = AdminForm::fromPost(['primary' => '#112233', 'surface' => '#AABBCC']);

        self::assertSame(['primary' => '#112233', 'surface' => '#aabbcc'], $values);
    }

    public function testDropsWhatIsNotAColour(): void
    {
        $values = AdminForm::fromPost([
            'primary' => 'rouge',
            'header_from' => '#1234',
            'header_to' => '<script>',
            'surface' => '#0369a0',
        ]);

        self::assertSame(['surface' => '#0369a0'], $values);
    }

    public function testIgnoresFieldsItDoesNotKnow(): void
    {
        $values = AdminForm::fromPost(['primary' => '#000000', 'admin' => '#ffffff', 'id' => '1']);

        self::assertSame(['primary' => '#000000'], $values);
    }

    public function testResetAsksForNothingToBeStored(): void
    {
        self::assertSame([], AdminForm::fromPost(['reset' => '1', 'primary' => '#112233']));
    }

    public function testEveryFieldOfTheFormIsAColourOfThePalette(): void
    {
        self::assertSame(array_keys(Palette::DEFAULTS), array_keys(AdminForm::FIELDS));
    }

    public function testTheFormShowsEveryColourItCanChange(): void
    {
        $page = AdminForm::body(Palette::settings([]), Page::URL, false);

        foreach (AdminForm::FIELDS as $field => $label) {
            self::assertStringContainsString("name=\"{$field}\"", $page);
            self::assertStringContainsString($label, $page);
        }
    }

    public function testTheFormNeverWritesAValueIntoThePageUnescaped(): void
    {
        $page = AdminForm::body(['primary' => '"><script>alert(1)</script>'], 'x', false);

        self::assertStringNotContainsString('<script>alert(1)', $page);
        self::assertStringContainsString('&lt;script&gt;', $page);
    }

    public function testSaysSoOnceTheColoursAreStored(): void
    {
        $values = Palette::settings([]);

        self::assertStringContainsString('enregistrées', AdminForm::body($values, 'x', true));
        self::assertStringNotContainsString('enregistrées', AdminForm::body($values, 'x', false));
    }

    public function testStoresADarkColourOnlyWhenItWasChanged(): void
    {
        $values = AdminForm::fromPost([
            'dark_surface' => '#000000', 'shown_dark_surface' => '#151c23',
            'dark_rail' => '#262626', 'shown_dark_rail' => '#262626',
        ]);

        self::assertSame(['dark_surface' => '#000000'], $values, 'une teinte calculée laissée telle quelle suit le mode clair');
    }

    public function testKeepsADarkColourChosenBefore(): void
    {
        $values = AdminForm::fromPost(['dark_rail' => '#262626', 'shown_dark_rail' => '#262626', 'kept_dark_rail' => '1']);

        self::assertSame(['dark_rail' => '#262626'], $values);
    }

    public function testRecalculatingForgetsTheDarkChoicesOnly(): void
    {
        $values = AdminForm::fromPost(['recalculate' => '1', 'primary' => '#112233', 'dark_surface' => '#000000', 'kept_dark_surface' => '1']);

        self::assertSame(['primary' => '#112233'], $values);
    }

    public function testTheFormShowsTheDarkModeWithItsComputedShades(): void
    {
        $page = AdminForm::body(Palette::settings([]), Page::URL, false, DarkPalette::settings([]), ['rail']);

        self::assertStringContainsString('Mode sombre', $page);
        foreach (array_keys(AdminForm::FIELDS) as $field) {
            self::assertStringContainsString("name=\"dark_{$field}\"", $page);
            self::assertStringContainsString("name=\"shown_dark_{$field}\"", $page);
        }
        self::assertStringContainsString('name="kept_dark_rail"', $page);
        self::assertStringNotContainsString('name="kept_dark_surface"', $page);
    }
}
