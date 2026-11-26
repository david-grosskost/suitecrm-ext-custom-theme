<?php

declare(strict_types=1);

namespace SuiteCrmExt\CustomTheme;

/**
 * The colours of the dark mode: the same six as the light palette, stored
 * under the same names with a "dark_" prefix.
 *
 * The administrator does not have to choose them. Until they do, each one is
 * worked out from its light counterpart, so a client who only set their
 * colours once gets a dark mode in those colours:
 *
 * - the page and the rail keep the hue of their light shade but go dark;
 * - the header keeps its gradient, darkened so white text still reads on it;
 * - the main colour and the blue keep the client's hue, and are only made
 *   lighter when they would not stand out against the dark page.
 *
 * A colour the administrator did choose is kept as it is: the dark mode is
 * theirs to adjust, and "recalculate" simply forgets what they chose.
 */
final class DarkPalette
{
    public const string PREFIX = 'dark_';

    /** Lightness of the page and of the rail: dark, the rail a step above. */
    public const float SURFACE_LIGHTNESS = 0.11;

    public const float RAIL_LIGHTNESS = 0.15;

    /** At most this light, for the two ends of the header's gradient. */
    public const float HEADER_FROM_LIGHTNESS = 0.28;

    public const float HEADER_TO_LIGHTNESS = 0.34;

    /**
     * Above this, a neutral keeps too much of its colour to look dark: the
     * page and the rail are made nearly grey.
     */
    public const float NEUTRAL_SATURATION = 0.25;

    /** Contrast asked of the main colour and of the blue against the page. */
    public const float ACCENT_CONTRAST = 3.0;

    /**
     * The dark palette, under the light palette's names: what the
     * administrator chose for the dark mode when it is a colour, worked out
     * from the light palette otherwise.
     *
     * @param array<string, mixed> $stored everything the administration stored
     *
     * @return array<string, string>
     */
    public static function settings(array $stored): array
    {
        // The page first: the accents are worked out against the page the
        // administrator chose, not against the one it replaces.
        $surface = $stored[self::PREFIX . 'surface'] ?? null;
        $derived = self::derive(
            Palette::settings($stored),
            Palette::isColour($surface) && is_string($surface) ? strtolower($surface) : null,
        );
        foreach (array_keys(Palette::DEFAULTS) as $name) {
            $value = $stored[self::PREFIX . $name] ?? null;
            if (Palette::isColour($value)) {
                /** @var string $value */
                $derived[$name] = strtolower($value);
            }
        }

        return $derived;
    }

    /**
     * The dark palette worked out from a light one, under the same names.
     *
     * @param array<string, string> $light   as Palette::settings returns it
     * @param string|null           $surface the dark page, when already chosen
     *
     * @return array<string, string>
     */
    public static function derive(array $light, ?string $surface = null): array
    {
        $surface ??= self::neutral($light['surface'], self::SURFACE_LIGHTNESS);

        return [
            'primary' => Colour::lightenedAgainst($light['primary'], $surface, self::ACCENT_CONTRAST),
            'header_from' => self::atMost($light['header_from'], self::HEADER_FROM_LIGHTNESS),
            'header_to' => self::atMost($light['header_to'], self::HEADER_TO_LIGHTNESS),
            'surface' => $surface,
            'rail' => self::neutral($light['rail'], self::RAIL_LIGHTNESS),
            'blue' => Colour::lightenedAgainst($light['blue'], $surface, self::ACCENT_CONTRAST),
        ];
    }

    /** A dark shade of a neutral: its hue, little of its saturation. */
    private static function neutral(string $colour, float $lightness): string
    {
        $hsl = Colour::toHsl($colour);

        return Colour::fromHsl($hsl['h'], min($hsl['s'], self::NEUTRAL_SATURATION), $lightness);
    }

    /** The colour, made darker if it is lighter than the given lightness. */
    private static function atMost(string $colour, float $lightness): string
    {
        return Colour::toHsl($colour)['l'] > $lightness ? Colour::withLightness($colour, $lightness) : $colour;
    }
}
