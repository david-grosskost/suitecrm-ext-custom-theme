<?php

declare(strict_types=1);

namespace SuiteCrmExt\CustomTheme;

/**
 * Which colours of SuiteCRM's own screens are handed over to the client, and
 * what they become.
 *
 * Those screens — the dashboard, Studio, the administration — are served in
 * an iframe, with the stylesheet of the legacy theme. The Angular module
 * never reaches them, and neither do the CSS variables it sets on the page
 * around them: an iframe is a document of its own.
 *
 * The overlay is therefore laid in the browser: the module reads the
 * stylesheet the iframe already loaded, keeps the rules that carry one of
 * these colours, swaps them, and puts the result in the iframe. Nothing is
 * written anywhere, which is not only simpler but the only way: the scanner
 * of the Module Loader refuses every filesystem call in a package, SuiteCRM's
 * own wrappers included.
 *
 * Only the table lives here, and it is served with the rest of the colours.
 * The browser decides nothing: one place, one answer, and the mapping can be
 * read without opening the front end.
 */
final class LegacyTheme
{
    /**
     * The colours of SuiteCRM this extension hands over, each with the
     * variable that drives it.
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
     * What the browser needs to lay the overlay: for each colour of SuiteCRM,
     * the variable that replaces it and the colour that variable holds.
     *
     * The original is sent along so the module can write
     * var(--name, #original): a variable that never arrives then leaves the
     * screen exactly as SuiteCRM drew it.
     *
     * @param array<string, string> $settings as stored by the administration
     *
     * @return array<string, array{variable: string, value: string}>
     */
    public static function overlay(array $settings): array
    {
        $values = self::values($settings);
        $overlay = [];
        foreach (self::COLOURS as $colour => $variable) {
            $overlay[$colour] = [
                'variable' => $variable,
                'value' => $values[$variable] ?? $colour,
            ];
        }

        return $overlay;
    }
}
