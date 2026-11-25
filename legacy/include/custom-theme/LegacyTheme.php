<?php

declare(strict_types=1);

namespace SuiteCrmExt\CustomTheme;

/**
 * The client's colours on the screens SuiteCRM still draws itself: the
 * dashboard, the quote and invoice records, Studio, the administration.
 *
 * Those screens are served in an iframe, with the stylesheet of the legacy
 * theme. Our Angular module never reaches them, and neither do the CSS
 * variables it sets on the page around them: an iframe is a document of its
 * own. So this extension ships a theme of its own, a child of suite8, and
 * gives it a stylesheet built from the one the instance already has.
 *
 * Built, not compiled. SuiteCRM ships Dawn's stylesheet ready-made beside its
 * SCSS sources, and a child theme does not inherit its parent's sub-theme CSS
 * (SugarTheme::getCSSURL prepends the sub-theme a second time when it asks
 * the parent, so it looks for Dawn/Dawn/style.css and finds nothing). The
 * child must therefore carry the whole stylesheet anyway — and the shortest
 * way there is to take the instance's own and hand six of its colours over to
 * CSS variables. No compiler, no pinned version, and it follows whatever
 * SuiteCRM the instance runs.
 */
final class LegacyTheme
{
    /** The theme's name, which is its directory under custom/themes/. */
    public const string NAME = 'CustomTheme';

    /** Where SuiteCRM keeps the stylesheet we start from, under public/legacy. */
    public const string SOURCE = 'themes/suite8/css/Dawn/style.css';

    /** Where this theme's own stylesheet goes, under public/legacy. */
    public const string TARGET = 'custom/themes/' . self::NAME . '/css/Dawn/style.css';

    /**
     * The colours of SuiteCRM this theme hands over to the client, each with
     * the variable that drives it.
     *
     * Six, chosen by what they paint rather than by how often they appear:
     * these are the ones a client sees on every screen. The greys, the whites
     * and the colours that mean something — red for an error, green for a
     * success — stay as SuiteCRM set them.
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
     * @param array<string, string> $settings as Palette::settings returns them
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
     * The stylesheet of this theme: the instance's own, with six colours
     * handed over to variables, and those variables declared at the top.
     *
     * The declaration is here rather than in the page because the legacy
     * screens are an iframe: variables set outside it never reach them. It is
     * written again whenever the colours change, which costs one pass over
     * the file.
     *
     * Each colour is replaced by var(--name, #original), so a variable that
     * never arrives leaves the screen exactly as SuiteCRM drew it. None of
     * the six appears inside a url() or a content: in SuiteCRM 8.10.2, where
     * a variable would not work; isPaintable() says so for the stylesheet at
     * hand, and the caller is expected to ask.
     *
     * It takes the settings an administrator chose, not the variables, so
     * that there is one way in and no way to hand it the wrong array.
     *
     * @param array<string, string> $settings as stored by the administration
     */
    public static function paint(string $dawn, array $settings): string
    {
        $declarations = '';
        foreach (self::values($settings) as $name => $colour) {
            $declarations .= $name . ':' . $colour . ';';
        }

        $painted = $dawn;
        foreach (self::COLOURS as $colour => $name) {
            $painted = (string) preg_replace(
                '/' . preg_quote($colour, '/') . '\b/i',
                'var(' . $name . ', ' . $colour . ')',
                $painted,
            );
        }

        $head = '/* ' . self::NAME . " */\n:root{" . $declarations . "}\n";

        // @charset has to be the first thing in a stylesheet or the browser
        // ignores it, so the block goes after it when Dawn declares one.
        if (preg_match('/^(@charset[^;]*;\s*)/i', $painted, $found) === 1) {
            return $found[1] . $head . substr($painted, strlen($found[1]));
        }

        return $head . $painted;
    }

    /**
     * Writes this theme's stylesheet in the instance, from the one SuiteCRM
     * has, and clears what it had cached of it.
     *
     * Returns what happened, in the words of whoever reads an installation
     * report. It never throws: a theme that cannot be built is a screen that
     * keeps SuiteCRM's colours, not an installation that fails.
     *
     * @param array<string, string> $settings as stored by the administration
     * @param string                $root     the legacy directory; '' for the current one
     */
    public static function write(array $settings, string $root = '', ?string $cached = null): string
    {
        $source = self::under($root, self::SOURCE);
        $dawn = is_file($source) ? (string) file_get_contents($source) : '';
        if ($dawn === '') {
            return "Thème historique non écrit : la feuille de style de SuiteCRM est introuvable ($source).";
        }
        if (!self::isPaintable($dawn)) {
            return 'Thème historique non écrit : la feuille de style de cette version de SuiteCRM '
                . "n'accepte pas les couleurs du client. Les écrans historiques gardent les siennes.";
        }

        $target = self::under($root, self::TARGET);
        if (!is_dir(dirname($target)) && !mkdir(dirname($target), 0755, true) && !is_dir(dirname($target))) {
            return "Thème historique non écrit : impossible de créer " . dirname($target) . '.';
        }
        if (file_put_contents($target, self::paint($dawn, $settings)) === false) {
            return "Thème historique non écrit : impossible d'écrire $target.";
        }

        // SuiteCRM keeps its own copy of a theme's stylesheet and serves that
        // one, so the new colours would wait for the cache to be cleared.
        if ($cached !== null && is_file($cached)) {
            unlink($cached);
        }

        return 'Thème historique écrit : les écrans de SuiteCRM prennent les couleurs du client.';
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

    private static function under(string $root, string $path): string
    {
        return $root === '' ? $path : rtrim($root, '/') . '/' . $path;
    }

    /**
     * Whether a stylesheet can be painted without breaking: none of the
     * colours may sit inside a url() or a content:, where a CSS variable has
     * no effect and would leave a broken value behind.
     *
     * A SuiteCRM whose stylesheet changed could fail this, and then the right
     * answer is to ship no theme rather than a broken one.
     */
    public static function isPaintable(string $dawn): bool
    {
        foreach (['/url\(([^)]*)\)/', '/content\s*:\s*("[^"]*"|\'[^\']*\')/'] as $pattern) {
            if (preg_match_all($pattern, $dawn, $matches) === false) {
                continue;
            }
            foreach ($matches[1] ?? [] as $inside) {
                foreach (array_keys(self::COLOURS) as $colour) {
                    if (stripos((string) $inside, $colour) !== false) {
                        return false;
                    }
                }
            }
        }

        return true;
    }
}
