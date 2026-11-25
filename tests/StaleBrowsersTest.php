<?php

declare(strict_types=1);

namespace SuiteCrmExt\CustomTheme\Tests;

use PHPUnit\Framework\TestCase;
use SuiteCrmExt\CustomTheme\StaleBrowsers;

/**
 * What tells browsers that the stylesheet has changed. Without it the colours
 * change on disk and nothing changes on screen: SuiteCRM builds the ?v= of
 * every stylesheet from this number alone.
 */
final class StaleBrowsersTest extends TestCase
{
    public function testRaisesTheNumberBrowsersGoBy(): void
    {
        self::assertSame(8, StaleBrowsers::applyTo(['js_custom_version' => 7])['js_custom_version']);
    }

    public function testCountsAnUnsetNumberAsOne(): void
    {
        // SuiteCRM reads a missing value as 1, so the next one is 2.
        self::assertSame(2, StaleBrowsers::applyTo([])['js_custom_version']);
    }

    public function testReadsTheNumberSuiteCrmWroteAsText(): void
    {
        self::assertSame(4, StaleBrowsers::applyTo(['js_custom_version' => '3'])['js_custom_version']);
    }

    public function testStartsOverOnSomethingThatIsNotANumber(): void
    {
        // Left alone, a value that cannot be raised would freeze the address
        // of every stylesheet for good.
        self::assertSame(1, StaleBrowsers::applyTo(['js_custom_version' => 'oui'])['js_custom_version']);
    }

    public function testLeavesTheRestOfTheConfigurationAlone(): void
    {
        $before = ['default_max_tabs' => 60, 'site_url' => 'https://exemple.test', 'js_custom_version' => 1];
        $after = StaleBrowsers::applyTo($before);

        unset($before['js_custom_version'], $after['js_custom_version']);
        self::assertSame($before, $after);
    }
}
