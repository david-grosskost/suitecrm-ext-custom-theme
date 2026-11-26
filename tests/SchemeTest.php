<?php

declare(strict_types=1);

namespace SuiteCrmExt\CustomTheme\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SuiteCrmExt\CustomTheme\Scheme;

#[CoversClass(Scheme::class)]
final class SchemeTest extends TestCase
{
    public function testKeepsTheThreeChoices(): void
    {
        foreach (Scheme::CHOICES as $choice) {
            self::assertSame($choice, Scheme::of($choice));
        }
    }

    public function testFollowsTheComputerForAnyoneWhoNeverChose(): void
    {
        foreach ([null, '', 'sombre', 'DARK', 1, ['dark']] as $value) {
            self::assertSame(Scheme::AUTO, Scheme::of($value));
        }
    }

    public function testAnswersInJson(): void
    {
        self::assertSame('{"scheme":"dark"}', Scheme::json('dark'));
        self::assertSame('{"scheme":"auto"}', Scheme::json('<script>'));
    }
}
