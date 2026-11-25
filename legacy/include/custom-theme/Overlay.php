<?php

declare(strict_types=1);

namespace SuiteCrmExt\CustomTheme;

/**
 * What the browser needs to lay an overlay over a stylesheet it did not
 * write: for each colour to hand over, the variable that replaces it and the
 * colour that variable holds.
 *
 * The original is sent along so the module can write var(--name, #original):
 * a variable that never arrives then leaves the screen exactly as SuiteCRM
 * drew it. The same shape serves the screens SuiteCRM draws itself and the
 * ones its Angular application draws, which is why it lives on its own.
 */
final class Overlay
{
    /**
     * @param array<string, string> $colours SuiteCRM's colour => the variable that replaces it
     * @param array<string, string> $values  the variable => the colour it holds
     *
     * @return array<string, array{variable: string, value: string}>
     */
    public static function of(array $colours, array $values): array
    {
        $overlay = [];
        foreach ($colours as $colour => $variable) {
            $overlay[$colour] = [
                'variable' => $variable,
                // Without a value the colour stands for itself: the screen is
                // left as it was rather than painted with something unasked.
                'value' => $values[$variable] ?? $colour,
            ];
        }

        return $overlay;
    }
}
