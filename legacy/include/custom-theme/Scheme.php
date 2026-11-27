<?php

declare(strict_types=1);

namespace SuiteCrmExt\CustomTheme;

/**
 * The light or dark choice of a user: light, dark, or as their computer is
 * set ("auto"). Kept in the user's SuiteCRM preferences, so it follows them
 * from one computer to another.
 */
final class Scheme
{
    public const string LIGHT = 'light';

    public const string DARK = 'dark';

    public const string AUTO = 'auto';

    /** The category of the user's preferences it is kept under. */
    public const string CATEGORY = 'custom_theme';

    public const string PREFERENCE = 'scheme';

    /** @var list<string> */
    public const array CHOICES = [self::LIGHT, self::DARK, self::AUTO];

    /**
     * A choice as stored or as posted: anything that is not one of the three
     * is "as the computer", the choice of someone who never chose.
     */
    public static function of(mixed $value): string
    {
        return is_string($value) && in_array($value, self::CHOICES, true) ? $value : self::AUTO;
    }

    /** The answer of the entry point: the choice, as JSON. */
    public static function json(string $scheme): string
    {
        return json_encode(['scheme' => self::of($scheme)], JSON_THROW_ON_ERROR);
    }
}
