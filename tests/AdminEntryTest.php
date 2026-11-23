<?php

declare(strict_types=1);

namespace SuiteCrmExt\CustomTheme\Tests;

use PHPUnit\Framework\TestCase;
use SuiteCrmExt\CustomTheme\Page;

/**
 * The entry this extension adds to the Administration page, read as SuiteCRM
 * reads it.
 *
 * Two mistakes are pinned down here, both of which emptied the module menu of
 * the whole instance with no trace in any log.
 *
 * The link once pointed at an entry point, a URL carrying no module.
 * SuiteCRM 8 hands every link of the administration page to its route
 * converter, which throws "No module defined" on such a URL; nothing catches
 * it, the appMetadata query fails whole, and the navigation disappears.
 *
 * And the file is loaded with a bare require from two handlers in the same
 * request, so it must bear being included twice: it may hold arrays and
 * nothing that cannot be declared a second time.
 */
final class AdminEntryTest extends TestCase
{
    private const string ENTRY = __DIR__
        . '/../legacy/Extension/modules/Administration/Ext/Administration/custom-theme.php';
    private const string LANGUAGE = __DIR__
        . '/../legacy/Extension/modules/Administration/Ext/Language/';
    private const string ACTION_MAP = __DIR__
        . '/../legacy/Extension/modules/Administration/Ext/ActionFileMap/custom-theme.php';

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
                Page::URL,
                'admin-themes',
            ]]],
            'LBL_CT_ADMIN_HEADER_DESC',
            'module_settings',
        ];

        self::assertSame($expected, self::headerGroups()[0] ?? null);
    }

    /**
     * The mistake that emptied the menu: a link whose URL carries no module.
     * SuiteCRM 8's route converter throws on it, and the navigation of the
     * whole instance goes with it.
     */
    public function testEveryLinkCarriesAModule(): void
    {
        $group = self::headerGroups()[0] ?? [];
        $defs = is_array($group) ? ($group[3] ?? []) : [];
        $links = is_array($defs) ? ($defs['Administration'] ?? []) : [];
        self::assertIsArray($links);
        self::assertNotEmpty($links, 'the entry shows at least one link');

        foreach ($links as $name => $link) {
            self::assertIsArray($link);
            $url = $link[3] ?? '';
            self::assertIsString($url);
            parse_str((string) parse_url($url, PHP_URL_QUERY), $params);
            self::assertArrayHasKey('module', $params, "the link $name carries no module");
            self::assertNotSame('', $params['module']);
        }
    }

    public function testTheActionItPointsAtIsMappedToItsFile(): void
    {
        /** @var array<string, string> $action_file_map */
        $action_file_map = [];
        require self::ACTION_MAP;

        self::assertSame(
            ['customtheme' => 'custom/include/custom-theme/AdminPage.php'],
            $action_file_map,
            'SuiteCRM looks the action up in lower case',
        );
        self::assertSame('customtheme', strtolower(Page::ACTION));
        self::assertFileExists(__DIR__ . '/../legacy/include/custom-theme/AdminPage.php');
    }

    public function testTheTemplateIsWhereTheViewLooksForIt(): void
    {
        // PageView::TEMPLATE, not loaded here (it needs SuiteCRM's SugarView).
        $view = (string) file_get_contents(__DIR__ . '/../legacy/include/custom-theme/PageView.php');

        self::assertStringContainsString("TEMPLATE = 'custom/include/custom-theme/page.tpl'", $view);
        self::assertFileExists(__DIR__ . '/../legacy/include/custom-theme/page.tpl');
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
