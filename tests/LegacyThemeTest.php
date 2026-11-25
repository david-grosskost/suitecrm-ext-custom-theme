<?php

declare(strict_types=1);

namespace SuiteCrmExt\CustomTheme\Tests;

use PHPUnit\Framework\TestCase;
use SuiteCrmExt\CustomTheme\LegacyTheme;
use SuiteCrmExt\CustomTheme\Theme;

/**
 * Which colours of SuiteCRM's own screens are handed over to the client.
 *
 * The overlay itself is laid in the browser, from the stylesheet the legacy
 * screen already loaded: nothing can be written from a package, the scanner
 * of the Module Loader refusing every filesystem call. What is checked here
 * is the table the browser is handed.
 */
final class LegacyThemeTest extends TestCase
{
    public function testHandsOverTheColoursSeenOnEveryScreen(): void
    {
        $colours = array_keys(LegacyTheme::COLOURS);

        self::assertContains('#f08377', $colours, 'SuiteCRM\'s coral');
        self::assertContains('#534d64', $colours, 'the title bar of a dashlet');
        self::assertContains('#93a4b3', $colours, 'the header row of a list');
        foreach ($colours as $colour) {
            self::assertMatchesRegularExpression('/^#[0-9a-f]{6}$/', $colour, 'written as the stylesheet writes it');
        }
    }

    public function testLeavesTheGreysAndTheMeaningfulColoursAlone(): void
    {
        // A red that means an error stays red whatever the client's colours.
        foreach (['#ffffff', '#f5f5f5', '#212529', '#d9534f', '#5cb85c'] as $untouched) {
            self::assertArrayNotHasKey($untouched, LegacyTheme::COLOURS);
        }
    }

    public function testEveryColourIsDrivenByAVariableOfItsOwn(): void
    {
        $variables = array_values(LegacyTheme::COLOURS);

        self::assertSame($variables, array_unique($variables), 'no two colours share a variable');
        foreach ($variables as $variable) {
            self::assertStringStartsWith('--ct-', $variable);
        }
    }

    public function testGivesEveryVariableAValue(): void
    {
        $values = LegacyTheme::values([]);

        self::assertSame(array_values(LegacyTheme::COLOURS), array_keys($values));
        foreach ($values as $variable => $colour) {
            self::assertMatchesRegularExpression('/^#[0-9a-f]{6}$/i', $colour, "$variable is a colour");
        }
    }

    public function testFollowsTheColoursTheAdministratorChose(): void
    {
        $values = LegacyTheme::values(['primary' => '#112233', 'header_from' => '#445566', 'header_to' => '#778899']);

        self::assertSame('#112233', $values['--ct-accent'], 'the coral follows the main colour');
        self::assertSame('#445566', $values['--ct-panel-head'], 'the dark bars follow the start of the gradient');
        self::assertSame(
            '#112233',
            $values['--ct-table-head'],
            'the head of a list follows the main colour: white on the end of the gradient, '
            . 'which is meant to be light, reads at 2.10 against the 4.5 asked of ordinary text',
        );
    }

    public function testSendsTheOriginalAlongAsAFallback(): void
    {
        // The browser writes var(--name, #original): a variable that never
        // arrives leaves the screen exactly as SuiteCRM drew it.
        $overlay = LegacyTheme::overlay(['primary' => '#112233']);

        self::assertSame(array_keys(LegacyTheme::COLOURS), array_keys($overlay));
        foreach ($overlay as $colour => $swap) {
            self::assertSame(LegacyTheme::COLOURS[$colour], $swap['variable']);
            self::assertMatchesRegularExpression('/^#[0-9a-f]{6}$/i', $swap['value']);
        }
        self::assertSame('#112233', $overlay['#f08377']['value']);
    }

    public function testTheBrowserIsHandedTheTableWithTheRest(): void
    {
        $payload = Theme::payload([]);

        self::assertArrayHasKey('legacy', $payload);
        self::assertSame(LegacyTheme::overlay([]), $payload['legacy']);
    }
}
