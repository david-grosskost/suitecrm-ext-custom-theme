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
    private const string REGISTRY = __DIR__
        . '/../legacy/Extension/application/Ext/EntryPointRegistry/custom-theme.php';

    /** Every label the entry names; the shape below is what uses them. */
    private const array LABELS = [
        'LBL_CT_ADMIN_HEADER',
        'LBL_CT_ADMIN_HEADER_DESC',
        'LBL_CT_ADMIN_TITLE',
        'LBL_CT_ADMIN_DESC',
    ];

    /**
     * The groups the file appends, as SuiteCRM collects them.
     *
     * @return list<array<int, mixed>>
     */
    private static function headerGroups(int $passes = 1): array
    {
        /** @var list<array<int, mixed>> $admin_group_header */
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
        $groups = self::headerGroups(2);

        self::assertCount(2, $groups, 'the file appends its group once per pass');
        self::assertSame($groups[0] ?? null, $groups[1] ?? null);
    }

    public function testAppendsItsGroupWithoutTouchingTheOthers(): void
    {
        /** @var list<array<int, mixed>> $admin_group_header */
        $admin_group_header = [['LBL_SOMEONE_ELSE', '', false, [], '', 'module_settings']];
        require self::ENTRY;

        self::assertCount(2, $admin_group_header, 'the group of another extension is kept');
        self::assertSame('LBL_SOMEONE_ELSE', $admin_group_header[0][0] ?? null);
        self::assertSame(self::headerGroups()[0] ?? null, $admin_group_header[1] ?? null);
    }

    public function testPointsAtTheScreenThatServesTheColours(): void
    {
        $expected = [
            'LBL_CT_ADMIN_HEADER',
            '',
            false,
            ['Administration' => ['custom_theme' => [
                'icon_AdminThemes',
                'LBL_CT_ADMIN_TITLE',
                'LBL_CT_ADMIN_DESC',
                './index.php?entryPoint=customThemeAdmin',
                'admin-themes',
            ]]],
            'LBL_CT_ADMIN_HEADER_DESC',
            'module_settings',
        ];

        self::assertSame($expected, self::headerGroups()[0] ?? null);
    }

    public function testTheScreenItPointsAtIsRegistered(): void
    {
        $registry = (string) file_get_contents(self::REGISTRY);

        self::assertStringContainsString("\$entry_point_registry['customThemeAdmin']", $registry);
    }

    /**
     * A label with no translation shows as an empty heading in the
     * administration page.
     */
    public function testEveryLabelIsTranslated(): void
    {
        foreach (['fr_FR', 'en_us'] as $language) {
            /** @var array<string, string> $mod_strings */
            $mod_strings = [];
            require self::LANGUAGE . $language . '.custom-theme.php';

            foreach (self::LABELS as $key) {
                self::assertArrayHasKey($key, $mod_strings, "$key is missing in $language");
                self::assertNotSame('', trim($mod_strings[$key]));
            }
        }
    }
}
