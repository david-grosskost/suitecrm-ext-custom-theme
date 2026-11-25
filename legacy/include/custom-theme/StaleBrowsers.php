<?php

declare(strict_types=1);

namespace SuiteCrmExt\CustomTheme;

/**
 * What tells browsers that a stylesheet has changed.
 *
 * SuiteCRM serves its stylesheets under a versioned address, ?v=<token>, and
 * builds that token from js_custom_version in the configuration — not from
 * the file, its date or its contents (include/utils.php, getVersionedPath).
 * So rewriting the stylesheet leaves the address untouched, and a browser
 * that already has it goes on serving its own copy: the colours change on
 * disk and nothing changes on screen, until someone thinks of emptying their
 * cache.
 *
 * Raising the number changes the address of every stylesheet and script at
 * once. That is what SuiteCRM's own "repair JS files" does, and it is the
 * only lever there is.
 */
final class StaleBrowsers
{
    /**
     * The configuration of the instance, with the number raised, so that the
     * next page asks for its stylesheets afresh.
     *
     * A value SuiteCRM has not set yet counts as 1, as SuiteCRM counts it.
     * Anything that is not a number is replaced rather than added to: an
     * administrator who wrote something odd there should not keep a token
     * that never moves again.
     *
     * @param array<string, mixed> $config
     *
     * @return array<string, mixed>
     */
    public static function applyTo(array $config): array
    {
        $raised = $config;
        $current = $config['js_custom_version'] ?? 1;
        $raised['js_custom_version'] = is_numeric($current) ? (int) $current + 1 : 1;

        return $raised;
    }
}
