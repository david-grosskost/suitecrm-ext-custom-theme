<?php

declare(strict_types=1);

namespace SuiteCrmExt\CustomTheme;

/**
 * The client's colours on the screens SuiteCRM still draws itself: the
 * dashboard, Studio, the administration, the records of the modules that have
 * no modern view yet.
 *
 * Those screens are served in an iframe, with the stylesheet of the legacy
 * theme. Our Angular module never reaches them, and neither do the CSS
 * variables it sets on the page around them: an iframe is a document of its
 * own.
 *
 * SuiteCRM has a place for this. After a theme's own stylesheet it appends
 * the one found under custom/ at the same path, so a file at
 * custom/themes/suite8/css/Dawn/style.css is read last and wins — same
 * selectors, later in the sheet. The instance stays on suite8, which matters
 * more than it sounds: on any other theme SuiteCRM 8 falls back to its old
 * record views, and the whole interface loses the modern ones.
 *
 * The file is built, not compiled, from the instance's own Dawn: the rules
 * that carry one of six colours are kept, those colours handed over to CSS
 * variables, and everything else left out. Around eighty kilobytes instead of
 * seven hundred, and it follows whatever SuiteCRM the instance runs.
 */
final class LegacyTheme
{
    /** Where SuiteCRM keeps the stylesheet we start from, under public/legacy. */
    public const string SOURCE = 'themes/suite8/css/Dawn/style.css';

    /** Where ours goes, under public/legacy: read after SuiteCRM's own. */
    public const string TARGET = 'custom/themes/suite8/css/Dawn/style.css';

    /** What SuiteCRM caches of the two put together, under public/legacy. */
    public const string CACHE = 'cache/themes/suite8/css/Dawn/style.css';

    /**
     * The colours of SuiteCRM this extension hands over to the client, each
     * with the variable that drives it.
     *
     * Six, chosen by what they paint rather than by how often they appear:
     * these are the ones seen on every screen. The greys, the whites and the
     * colours that mean something — red for an error, green for a success —
     * stay as SuiteCRM set them.
     *
     * @var array<string, string>
     */
    public const array COLOURS = [
        '#f08377' => '--ct-accent',        // SuiteCRM's coral: buttons, links, the eye-catching parts
        '#93a4b3' => '--ct-table-head',    // the header row of every list
        '#534d64' => '--ct-panel-head',    // the title bar of a dashlet or a panel
        '#94a6b5' => '--ct-button',        // ordinary buttons
        '#76858f' => '--ct-surface',       // panel surfaces and toolbars
        '#66727d' => '--ct-dashlet-head',  // the head of a dashlet
    ];

    /**
     * What each variable is worth, drawn from the colours the administrator
     * chose. The mapping is a matter of taste rather than of technique, and
     * it is meant to be changed by eye:
     *
     * - the coral is SuiteCRM's own signature, so it follows the client's
     *   main colour;
     * - the dark bars follow the start of the header's gradient, the lighter
     *   table heads its end, so that a screen holds together;
     * - the buttons follow the main colour too, being what one clicks.
     *
     * @param array<string, string> $settings as stored by the administration
     *
     * @return array<string, string> variable name => colour
     */
    public static function values(array $settings): array
    {
        $settings = Palette::settings($settings);
        $primary = $settings['primary'] ?? Palette::DEFAULTS['primary'];
        $from = $settings['header_from'] ?? Palette::DEFAULTS['header_from'];
        $to = $settings['header_to'] ?? Palette::DEFAULTS['header_to'];

        return [
            '--ct-accent' => $primary,
            '--ct-table-head' => $to,
            '--ct-panel-head' => $from,
            '--ct-button' => $primary,
            '--ct-surface' => $from,
            '--ct-dashlet-head' => $from,
        ];
    }

    /**
     * The stylesheet to lay over SuiteCRM's: the rules of Dawn that carry one
     * of the six colours, those colours handed over to variables, and the
     * variables declared at the top.
     *
     * The declaration belongs here rather than in the page because the legacy
     * screens are an iframe: variables set outside it never reach them.
     *
     * Each colour becomes var(--name, #original), so a variable that never
     * arrives leaves the screen exactly as SuiteCRM drew it.
     *
     * @param array<string, string> $settings as stored by the administration
     */
    public static function paint(string $dawn, array $settings): string
    {
        $declarations = '';
        foreach (self::values($settings) as $name => $colour) {
            $declarations .= $name . ':' . $colour . ';';
        }

        // Comments are dropped first: none of them is wanted in the overlay,
        // and one holding a brace would throw the reading of the blocks off.
        $rules = (string) preg_replace('#/\*.*?\*/#s', '', $dawn);

        return "/* custom-theme */\n:root{" . $declarations . "}\n" . self::keepColouredRules($rules);
    }

    /**
     * The rules that carry one of the six colours, with the colours replaced
     * and everything else dropped.
     *
     * A rule nested in an @media or an @supports keeps its wrapper: taken out
     * of it, a rule meant for one screen width would apply to every one.
     * Wrappers left with nothing inside are dropped whole.
     */
    private static function keepColouredRules(string $css): string
    {
        $kept = '';
        $length = strlen($css);
        $at = 0;

        while ($at < $length) {
            $opens = strpos($css, '{', $at);
            if ($opens === false) {
                break;
            }
            // An at-statement (@charset, @import) ends before any block.
            $ends = strpos($css, ';', $at);
            if ($ends !== false && $ends < $opens) {
                $at = $ends + 1;

                continue;
            }

            $prelude = trim(substr($css, $at, $opens - $at));
            $closes = self::matchingBrace($css, $opens);
            $body = substr($css, $opens + 1, $closes - $opens - 1);
            $at = $closes + 1;

            // A block holding blocks is a wrapper: @media, @supports.
            if (str_starts_with($prelude, '@') && str_contains($body, '{')) {
                $inside = self::keepColouredRules($body);
                if ($inside !== '') {
                    $kept .= $prelude . '{' . $inside . '}';
                }

                continue;
            }

            foreach (array_keys(self::COLOURS) as $colour) {
                if (stripos($body, $colour) !== false) {
                    $kept .= $prelude . '{' . self::handOver($body) . '}';

                    break;
                }
            }
        }

        return $kept;
    }

    /** The colours of a declaration block, handed over to their variables. */
    private static function handOver(string $body): string
    {
        foreach (self::COLOURS as $colour => $name) {
            $body = (string) preg_replace(
                '/' . preg_quote($colour, '/') . '\b/i',
                'var(' . $name . ', ' . $colour . ')',
                $body,
            );
        }

        return $body;
    }

    /** Where the block opened at $opens closes. */
    private static function matchingBrace(string $css, int $opens): int
    {
        $depth = 0;
        $length = strlen($css);
        for ($at = $opens; $at < $length; $at++) {
            if ($css[$at] === '{') {
                $depth++;
            } elseif ($css[$at] === '}') {
                $depth--;
                if ($depth === 0) {
                    return $at;
                }
            }
        }

        return $length - 1;
    }

    /**
     * Writes the stylesheet in the instance and clears what SuiteCRM had
     * cached of Dawn, which holds a copy of it.
     *
     * Returns what happened, in the words of whoever reads an installation
     * report. It never throws: a stylesheet that cannot be written is a
     * screen that keeps SuiteCRM's colours, not an installation that fails.
     *
     * @param array<string, string> $settings as stored by the administration
     * @param string                $root     the legacy directory; '' for the current one
     */
    public static function write(array $settings, string $root = ''): string
    {
        $source = self::under($root, self::SOURCE);
        $dawn = is_file($source) ? (string) file_get_contents($source) : '';
        if ($dawn === '') {
            return "Écrans historiques inchangés : la feuille de style de SuiteCRM est introuvable ($source).";
        }
        if (!self::isPaintable($dawn)) {
            return 'Écrans historiques inchangés : la feuille de style de cette version de SuiteCRM '
                . "n'accepte pas les couleurs du client.";
        }

        $target = self::under($root, self::TARGET);
        $directory = dirname($target);
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            return "Écrans historiques inchangés : impossible de créer $directory.";
        }
        if (file_put_contents($target, self::paint($dawn, $settings)) === false) {
            return "Écrans historiques inchangés : impossible d'écrire $target.";
        }

        self::forgetTheCache($root);

        return 'Écrans historiques aux couleurs du client.';
    }

    /**
     * Drops what SuiteCRM cached of Dawn, which it built by putting its own
     * stylesheet and ours together: left alone, it would go on being served.
     */
    public static function forgetTheCache(string $root = ''): void
    {
        $cached = self::under($root, self::CACHE);
        if (is_file($cached)) {
            unlink($cached);
        }
    }

    /**
     * Whether the stylesheet should be written again: missing, or older than
     * the one it is built from, which is what an upgrade of SuiteCRM leaves
     * behind.
     */
    public static function isStale(string $root = ''): bool
    {
        $source = self::under($root, self::SOURCE);
        $target = self::under($root, self::TARGET);
        if (!is_file($source)) {
            return false;
        }

        return !is_file($target) || filemtime($target) < filemtime($source);
    }

    /**
     * Whether a stylesheet can be painted without breaking: none of the
     * colours may sit inside a url() or a content:, where a CSS variable has
     * no effect and would leave a broken value behind.
     *
     * A SuiteCRM whose stylesheet changed could fail this, and then the right
     * answer is to leave its colours alone rather than break its screens.
     */
    public static function isPaintable(string $dawn): bool
    {
        foreach (['/url\(([^)]*)\)/', '/content\s*:\s*("[^"]*"|\'[^\']*\')/'] as $pattern) {
            if (preg_match_all($pattern, $dawn, $matches) === false) {
                continue;
            }
            foreach ($matches[1] as $inside) {
                foreach (array_keys(self::COLOURS) as $colour) {
                    if (stripos($inside, $colour) !== false) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    private static function under(string $root, string $path): string
    {
        return $root === '' ? $path : rtrim($root, '/') . '/' . $path;
    }
}
