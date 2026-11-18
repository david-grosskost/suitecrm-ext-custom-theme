<?php

declare(strict_types=1);

namespace SuiteCrmExt\CustomTheme\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SuiteCrmExt\CustomTheme\Navigation;

#[CoversClass(Navigation::class)]
final class NavigationTest extends TestCase
{
    public function testLiftsTheCapOnTheModulesTheMenuShows(): void
    {
        $config = Navigation::applyTo([]);

        self::assertSame(Navigation::TABS, $config['default_max_tabs']);
    }

    public function testLeavesEverythingElseAlone(): void
    {
        $config = Navigation::applyTo(['default_language' => 'fr_FR', 'dbconfig' => ['db_name' => 'crm']]);

        self::assertSame('fr_FR', $config['default_language']);
        self::assertSame(['db_name' => 'crm'], $config['dbconfig']);
    }

    public function testRaisesTheCapWellAboveWhatAnInstanceShows(): void
    {
        // SuiteCRM's own cap is eight: the rail would fold most of a standard
        // instance's modules away, and a folded module loses its submenu.
        self::assertGreaterThan(40, Navigation::TABS);
    }
}
