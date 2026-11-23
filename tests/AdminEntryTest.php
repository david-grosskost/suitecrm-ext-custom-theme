<?php

declare(strict_types=1);

namespace SuiteCrmExt\CustomTheme\Tests;

use PHPUnit\Framework\TestCase;

/**
 * The entry this extension adds to the Administration page, read as SuiteCRM
 * reads it.
 *
 * SuiteCRM assembles the file below into adminpaneldefs.php and loads it with
 * a bare require from two handlers in the same request, so the file must bear
 * being included twice. It once held something that could not be declared
 * twice; the fatal error that followed emptied the whole module menu, and the
 * entry was removed rather than repaired. These tests keep that from coming
 * back.
 */
final class AdminEntryTest extends TestCase
{
    private const string ENTRY = __DIR__
        . '/../legacy/Extension/modules/Administration/Ext/Administration/custom-theme.php';
    private const string LANGUAGE = __DIR__
        . '/../legacy/Extension/modules/Administration/Ext/Language/';

    /**
     * The groups the file appends, as SuiteCRM collects them.
     *
     * @return array<int, array<int, mixed>>
     */
    private static function groups(int $passes = 1): array
    {
        $admin_group_header = [];
        for ($pass = 0; $pass < $passes; $pass++) {
            require self::ENTRY;
        }

        return $admin_group_header;
    }

    public function testSurvivesBeingIncludedTwiceInOneRequest(): void
    {
        // A fatal error here is the failure this test exists for; it would
        // stop PHPUnit outright, which is the point.
        $groups = self::groups(2);

        self::assertCount(2, $groups, 'the file appends its group once per pass');
    }

    public function testAppendsItsGroupWithoutTouchingTheOthers(): void
    {
        $admin_group_header = [['LBL_SOMEONE_ELSE', '', false, [], '', 'module_settings']];
        require self::ENTRY;

        self::assertCount(2, $admin_group_header, 'the group of another extension is kept');
        self::assertSame('LBL_SOMEONE_ELSE', $admin_group_header[0][0]);
        self::assertSame('LBL_CT_ADMIN_HEADER', $admin_group_header[1][0]);
    }

    public function testPointsAtTheScreenThatServesTheColours(): void
    {
        $links = self::groups()[0][3]['Administration'] ?? [];

        self::assertArrayHasKey('custom_theme', $links);
        self::assertSame('./index.php?entryPoint=customThemeAdmin', $links['custom_theme'][3]);

        $registry = (string) file_get_contents(
            __DIR__ . '/../legacy/Extension/application/Ext/EntryPointRegistry/custom-theme.php'
        );
        self::assertStringContainsString("\$entry_point_registry['customThemeAdmin']", $registry);
    }

    /**
     * Every label the entry names must exist in both languages, or the
     * administration page shows an empty heading.
     */
    public function testEveryLabelIsTranslated(): void
    {
        $group = self::groups()[0];
        $link = $group[3]['Administration']['custom_theme'];
        $keys = [$group[0], $group[4], $link[1], $link[2]];

        foreach (['fr_FR', 'en_us'] as $language) {
            $mod_strings = [];
            require self::LANGUAGE . $language . '.custom-theme.php';

            foreach ($keys as $key) {
                self::assertArrayHasKey($key, $mod_strings, "$key is missing in $language");
                self::assertNotSame('', trim($mod_strings[$key]));
            }
        }
    }
}
