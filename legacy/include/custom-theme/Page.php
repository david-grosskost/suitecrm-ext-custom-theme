<?php

declare(strict_types=1);

namespace SuiteCrmExt\CustomTheme;

/**
 * Where the colours screen lives in SuiteCRM, in one place: the legacy action
 * that serves it, and the URL that reaches it.
 *
 * The URL carries a module, and that is not decoration. A link of the
 * administration page is handed to SuiteCRM 8's route converter, which throws
 * on a URL without one; the exception takes the whole navigation down with it.
 */
final class Page
{
    /** Legacy action of the page, in the Administration module (ActionFileMap). */
    public const string ACTION = 'CustomTheme';

    /** The page's own URL, used by the administration link and by the form. */
    public const string URL = './index.php?module=Administration&action=' . self::ACTION;

    /**
     * A label of the Administration module, or the key itself when the
     * language file is missing, so a screen is never titled with nothing.
     *
     * @param array<mixed> $labels
     */
    public static function label(array $labels, string $key): string
    {
        $label = $labels[$key] ?? null;

        return is_string($label) && trim($label) !== '' ? $label : $key;
    }
}
