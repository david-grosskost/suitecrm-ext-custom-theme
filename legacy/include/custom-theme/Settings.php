<?php

declare(strict_types=1);

namespace SuiteCrmExt\CustomTheme;

/** Où les couleurs choisies sont gardées. */
interface Settings
{
    /** @return array<string, string> */
    public function read(): array;

    /** @param array<string, string> $values */
    public function replace(array $values): void;
}
