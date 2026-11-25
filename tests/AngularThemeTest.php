<?php

declare(strict_types=1);

namespace SuiteCrmExt\CustomTheme\Tests;

use PHPUnit\Framework\TestCase;
use SuiteCrmExt\CustomTheme\AngularTheme;
use SuiteCrmExt\CustomTheme\Palette;
use SuiteCrmExt\CustomTheme\Theme;

/**
 * Which colours of the screens SuiteCRM's own application draws are handed
 * over to the client, and which are left alone.
 *
 * The overlay is laid in the browser, from the stylesheet the application
 * already loaded; what is checked here is the table the browser is handed.
 */
final class AngularThemeTest extends TestCase
{
    public function testHandsOverTheColoursOfItsPalette(): void
    {
        $colours = array_keys(AngularTheme::COLOURS);

        self::assertContains('#534d64', $colours, 'its main colour');
        self::assertContains('#8d74cc', $colours, 'its complementary colour');
        self::assertContains('#ef8177', $colours, 'the coral');
        self::assertContains('#f08377', $colours, 'the coral, second shade');
        foreach ($colours as $colour) {
            self::assertMatchesRegularExpression('/^#[0-9a-f]{6}$/', $colour, 'written as the stylesheet writes it');
        }
    }

    public function testBothShadesOfTheCoralShareOneVariable(): void
    {
        // The two differ by one point of red and nobody would tell them
        // apart, so handing them different colours would only show as a seam.
        self::assertSame(AngularTheme::COLOURS['#ef8177'], AngularTheme::COLOURS['#f08377']);
    }

    public function testLeavesTheMeaningfulColoursAlone(): void
    {
        foreach (['#ffffff', '#dc3545', '#28a745', '#ffc107'] as $untouched) {
            self::assertArrayNotHasKey($untouched, AngularTheme::COLOURS, 'red means an error whatever the client chose');
        }
    }

    public function testGivesEveryVariableAValue(): void
    {
        $values = AngularTheme::values([]);

        foreach (AngularTheme::COLOURS as $colour => $variable) {
            self::assertArrayHasKey($variable, $values, "$colour has nothing to become");
            self::assertMatchesRegularExpression('/^#[0-9a-f]{6}$/i', $values[$variable]);
        }
    }

    public function testFollowsTheColoursTheAdministratorChose(): void
    {
        $values = AngularTheme::values(['primary' => '#112233', 'header_from' => '#445566', 'header_to' => '#778899']);

        self::assertSame('#445566', $values['--ct-ng-main'], 'its main colour follows the start of the gradient');
        self::assertSame('#112233', $values['--ct-ng-accent'], 'the coral follows the main colour');
        self::assertSame('#112233', $values['--ct-ng-complementary'], 'and so does the complementary');
    }

    public function testTheHeadOfAListIsReadable(): void
    {
        // White on SuiteCRM's own grey reads at 2.56, under the 4.5 asked of
        // ordinary text; on an end of gradient meant to be light it fell to
        // 2.10. The main colour is the darker choice, and the one kept.
        $values = AngularTheme::values(['primary' => '#112233', 'header_to' => '#eeeeee']);

        self::assertSame('#112233', $values['--ct-ng-light']);
    }

    public function testLeavesTheBlueAsItIsUntilItIsChosen(): void
    {
        // Bootstrap's blue carries the focus ring as well as the buttons: a
        // colour chosen there can make a screen unreadable, so it moves only
        // when an administrator asks.
        $untouched = AngularTheme::overlay([]);

        self::assertSame('#007bff', $untouched['#007bff']['value'], 'the default leaves it where SuiteCRM put it');
        self::assertSame('#007bff', Palette::DEFAULTS['blue']);

        $chosen = AngularTheme::overlay(['blue' => '#aa0000']);

        self::assertSame('#aa0000', $chosen['#007bff']['value']);
    }

    public function testSendsTheOriginalAlongAsAFallback(): void
    {
        $overlay = AngularTheme::overlay(['primary' => '#112233']);

        self::assertSame(array_keys(AngularTheme::COLOURS), array_keys($overlay));
        foreach ($overlay as $colour => $swap) {
            self::assertSame(AngularTheme::COLOURS[$colour], $swap['variable']);
            self::assertMatchesRegularExpression('/^#[0-9a-f]{6}$/i', $swap['value']);
        }
    }

    public function testTheBrowserIsHandedTheTableWithTheRest(): void
    {
        $payload = Theme::payload([]);

        self::assertArrayHasKey('angular', $payload);
        self::assertSame(AngularTheme::overlay([]), $payload['angular']);
    }

    public function testTheTwoLayersDoNotShareTheirVariables(): void
    {
        // Each layer is laid in a document of its own; sharing names would
        // make a change to one show up, unasked, in the other.
        $here = array_values(AngularTheme::COLOURS);
        $there = array_values(\SuiteCrmExt\CustomTheme\LegacyTheme::COLOURS);

        self::assertSame([], array_intersect($here, $there));
    }
}
