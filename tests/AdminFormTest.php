<?php

declare(strict_types=1);

namespace SuiteCrmExt\CustomTheme\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SuiteCrmExt\CustomTheme\AdminForm;
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
        $page = AdminForm::page(Palette::settings([]), 'index.php?entryPoint=customThemeAdmin', false);

        foreach (AdminForm::FIELDS as $field => $label) {
            self::assertStringContainsString("name=\"{$field}\"", $page);
            self::assertStringContainsString($label, $page);
        }
    }

    public function testTheFormNeverWritesAValueIntoThePageUnescaped(): void
    {
        $page = AdminForm::page(['primary' => '"><script>alert(1)</script>'], 'x', false);

        self::assertStringNotContainsString('<script>alert(1)', $page);
        self::assertStringContainsString('&lt;script&gt;', $page);
    }

    public function testSaysSoOnceTheColoursAreStored(): void
    {
        $values = Palette::settings([]);

        self::assertStringContainsString('enregistrées', AdminForm::page($values, 'x', true));
        self::assertStringNotContainsString('enregistrées', AdminForm::page($values, 'x', false));
    }
}
