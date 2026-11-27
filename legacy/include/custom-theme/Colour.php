<?php

declare(strict_types=1);

namespace SuiteCrmExt\CustomTheme;

/**
 * Colour arithmetic, on the six-digit codes the theme stores: lightness to
 * darken a palette, luminance and contrast to check it stays readable.
 *
 * Contrast follows WCAG 2: 4.5 for ordinary text, 3 for large text and for
 * the parts of the interface that are not text (a button against its page).
 */
final class Colour
{
    /**
     * Hue (0-360), saturation and lightness (0-1) of a colour.
     *
     * @return array{h: float, s: float, l: float}
     */
    public static function toHsl(string $hex): array
    {
        [$red, $green, $blue] = self::channels($hex);
        $max = max($red, $green, $blue);
        $min = min($red, $green, $blue);
        $lightness = ($max + $min) / 2;

        if ($max === $min) {
            return ['h' => 0.0, 's' => 0.0, 'l' => $lightness];
        }

        $delta = $max - $min;
        $saturation = $delta / (1 - abs(2 * $lightness - 1));
        if ($max === $red) {
            $hue = fmod(($green - $blue) / $delta + 6, 6);
        } elseif ($max === $green) {
            $hue = ($blue - $red) / $delta + 2;
        } else {
            $hue = ($red - $green) / $delta + 4;
        }

        return ['h' => $hue * 60, 's' => min(1.0, $saturation), 'l' => $lightness];
    }

    /** The six-digit code of a hue (0-360), saturation and lightness (0-1). */
    public static function fromHsl(float $hue, float $saturation, float $lightness): string
    {
        $saturation = max(0.0, min(1.0, $saturation));
        $lightness = max(0.0, min(1.0, $lightness));
        $chroma = (1 - abs(2 * $lightness - 1)) * $saturation;
        $sector = fmod($hue, 360) / 60;
        $second = $chroma * (1 - abs(fmod($sector, 2) - 1));
        $match = $lightness - $chroma / 2;

        [$red, $green, $blue] = match (true) {
            $sector < 1 => [$chroma, $second, 0.0],
            $sector < 2 => [$second, $chroma, 0.0],
            $sector < 3 => [0.0, $chroma, $second],
            $sector < 4 => [0.0, $second, $chroma],
            $sector < 5 => [$second, 0.0, $chroma],
            default => [$chroma, 0.0, $second],
        };

        $hex = '#';
        foreach ([$red, $green, $blue] as $channel) {
            $hex .= sprintf('%02x', (int) round(($channel + $match) * 255));
        }

        return $hex;
    }

    /** The same colour at another lightness (0-1), hue and saturation kept. */
    public static function withLightness(string $hex, float $lightness): string
    {
        $hsl = self::toHsl($hex);

        return self::fromHsl($hsl['h'], $hsl['s'], $lightness);
    }

    /** Relative luminance, as WCAG 2 defines it (0 for black, 1 for white). */
    public static function luminance(string $hex): float
    {
        $linear = [];
        foreach (self::channels($hex) as $channel) {
            $linear[] = $channel <= 0.04045 ? $channel / 12.92 : (($channel + 0.055) / 1.055) ** 2.4;
        }

        return 0.2126 * $linear[0] + 0.7152 * $linear[1] + 0.0722 * $linear[2];
    }

    /** Contrast ratio between two colours, from 1 (none) to 21 (black on white). */
    public static function contrast(string $first, string $second): float
    {
        $lighter = max(self::luminance($first), self::luminance($second));
        $darker = min(self::luminance($first), self::luminance($second));

        return ($lighter + 0.05) / ($darker + 0.05);
    }

    /**
     * The colour lightened step by step until it stands out against the
     * background by the given ratio, hue and saturation kept. Gives up at
     * the lightest shade that still has a colour, rather than at white.
     */
    public static function lightenedAgainst(string $hex, string $background, float $ratio): string
    {
        $hsl = self::toHsl($hex);
        $colour = strtolower($hex);
        for ($lightness = $hsl['l']; self::contrast($colour, $background) < $ratio && $lightness < 0.85; $lightness += 0.02) {
            $colour = self::fromHsl($hsl['h'], $hsl['s'], $lightness + 0.02);
        }

        return $colour;
    }

    /** @return array{float, float, float} red, green, blue, 0-1 */
    private static function channels(string $hex): array
    {
        $digits = ltrim($hex, '#');

        return [
            hexdec(substr($digits, 0, 2)) / 255,
            hexdec(substr($digits, 2, 2)) / 255,
            hexdec(substr($digits, 4, 2)) / 255,
        ];
    }
}
