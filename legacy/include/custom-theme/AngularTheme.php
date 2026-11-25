<?php

declare(strict_types=1);

namespace SuiteCrmExt\CustomTheme;

/**
 * The colours of the screens SuiteCRM's own application draws: the lists, the
 * record views, the administration. They are not the ones of the screens it
 * draws in an iframe, and they are not named after what they paint.
 *
 * SuiteCRM names them by their part in its palette, and its own class names
 * say so: .text-main and .fill-main for one, .fill-complementary for another.
 * None of them belongs to a kind of element -- each of the five crosses the
 * buttons, the tables, the tabs and the links alike. A setting called "the
 * colour of the links" would therefore be a lie, which is why the client
 * chooses a palette and not a list of parts.
 *
 * The blue is apart. It is Bootstrap's, SuiteCRM takes it as it comes, and it
 * carries the focus ring as well as the buttons: a client colour there can
 * make a screen unreadable. So it has a setting of its own, whose default is
 * the blue itself -- nothing changes until the administrator chooses.
 */
final class AngularTheme
{
    /**
     * SuiteCRM's colour => the variable that replaces it. Counted in
     * 8.10.2's stylesheet: 101 rules for the first, 141 for the blue.
     *
     * @var array<string, string>
     */
    public const array COLOURS = [
        '#534d64' => '--ct-ng-main',           // its main colour: text, icons, bars
        '#8d74cc' => '--ct-ng-complementary',  // the active tab, the second accents
        '#ef8177' => '--ct-ng-accent',         // the coral, in its two shades
        '#f08377' => '--ct-ng-accent',
        '#93a4b3' => '--ct-ng-light',          // the light grey of the list heads
        '#76858f' => '--ct-ng-grey',           // the grey of the surfaces
        '#007bff' => '--ct-ng-blue',           // Bootstrap's, left alone by default
    ];

    /**
     * What each variable is worth, from what the administrator chose.
     *
     * The palette holds together because the colours keep their parts: what
     * SuiteCRM used to draw attention follows the main colour, and what it
     * used for its bars follows the start of the header's gradient.
     *
     * The head of a list follows the main colour rather than the end of the
     * gradient, which is the lighter of the two. White on SuiteCRM's own
     * grey reads at 2.56, under the 4.5 asked of ordinary text; on the end
     * of a gradient meant to be light it fell to 2.10, and on the main
     * colour it reads at 4.11. The end of the gradient keeps the navbar,
     * where it is read against nothing.
     *
     * @param array<string, mixed> $settings as stored by the administration
     *
     * @return array<string, string> variable name => colour
     */
    public static function values(array $settings): array
    {
        $settings = Palette::settings($settings);

        return [
            '--ct-ng-main' => $settings['header_from'],
            '--ct-ng-complementary' => $settings['primary'],
            '--ct-ng-accent' => $settings['primary'],
            '--ct-ng-light' => $settings['primary'],
            '--ct-ng-grey' => $settings['header_from'],
            '--ct-ng-blue' => $settings['blue'],
        ];
    }

    /**
     * @param array<string, mixed> $settings as stored by the administration
     *
     * @return array<string, array{variable: string, value: string}>
     */
    public static function overlay(array $settings): array
    {
        return Overlay::of(self::COLOURS, self::values($settings));
    }
}
