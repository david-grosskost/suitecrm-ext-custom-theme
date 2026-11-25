<?php

declare(strict_types=1);

namespace SuiteCrmExt\CustomTheme;

/**
 * Les couleurs du thème : celles que l'administrateur choisit, et celles
 * qu'on en déduit.
 *
 * Deux familles, pour deux raisons différentes :
 *
 * - les couleurs de l'entreprise (primaire, dégradé de l'en-tête) sont
 *   choisies dans l'administration : elles changent d'un client à l'autre ;
 * - l'accent de chaque module est déduit de son nom, parce que configurer
 *   vingt couleurs à la main serait pénible et que le choix n'a pas
 *   d'importance : ce qui compte est qu'un module garde toujours la même.
 */
final class Palette
{
    /** Réglages par défaut, si l'administrateur n'a rien choisi. */
    public const array DEFAULTS = [
        'primary' => '#0284c5',
        'header_from' => '#0369a0',
        'header_to' => '#3abff8',
        'surface' => '#f1f5f9',
        'rail' => '#ffffff',
        // Le bleu que SuiteCRM utilise partout : boutons, liens, champs
        // actifs. Sa valeur par défaut est celle de SuiteCRM, pour que rien
        // ne change tant que l'administrateur ne l'a pas choisie.
        'blue' => '#007bff',
    ];

    /**
     * Les accents possibles d'un module. Choisis pour rester lisibles côte à
     * côte sur un rail blanc, et distincts les uns des autres.
     *
     * @var list<string>
     */
    public const array ACCENTS = [
        '#7c3aed', '#0ea5e9', '#f59e0b', '#ec4899',
        '#10b981', '#f97316', '#6366f1', '#14b8a6',
        '#ef4444', '#8b5cf6', '#22c55e', '#eab308',
    ];

    /** Un code couleur hexadécimal à six chiffres, le seul format accepté. */
    public static function isColour(mixed $value): bool
    {
        return is_string($value) && preg_match('/^#[0-9a-f]{6}$/i', $value) === 1;
    }

    /**
     * Les réglages complets : ceux de l'administrateur quand ils sont
     * valides, ceux par défaut sinon. Une valeur abîmée en base ne casse
     * jamais l'interface, elle est simplement ignorée.
     *
     * @param array<string, mixed> $stored
     *
     * @return array<string, string>
     */
    public static function settings(array $stored): array
    {
        $settings = self::DEFAULTS;
        foreach (self::DEFAULTS as $name => $default) {
            $value = $stored[$name] ?? null;
            if (self::isColour($value)) {
                /** @var string $value */
                $settings[$name] = strtolower($value);
            }
        }

        return $settings;
    }

    /**
     * L'accent du module de rang donné. Les modules connus sont servis dans
     * l'ordre, ce qui garantit que les premiers — ceux qu'on voit tous les
     * jours — ont tous une couleur différente. Tirer au sort depuis le nom
     * donnait quatre couleurs pour huit modules courants : des collisions
     * qui ruinent l'intérêt de colorer.
     */
    public static function accentAt(int $position): string
    {
        $accents = self::ACCENTS;

        return $accents[abs($position) % count($accents)];
    }

    /**
     * L'accent d'un module qu'on ne connaît pas — un module créé dans Studio,
     * absent de la liste. Déduit de son nom, donc toujours le même pour un nom
     * donné, sur toutes les instances et d'une version à l'autre.
     */
    public static function accentFor(string $module): string
    {
        return self::accentAt((int) crc32(strtolower($module)));
    }

    /**
     * Les variables CSS à poser sur la page. SuiteCRM 8 est entièrement
     * piloté par variables : les redéfinir suffit à reteinter l'interface,
     * sans toucher à une seule règle de SuiteCRM.
     *
     * @param array<string, mixed> $stored
     *
     * @return array<string, string>
     */
    public static function variables(array $stored): array
    {
        $settings = self::settings($stored);

        return [
            '--primary' => $settings['primary'],
            '--primary-color' => $settings['primary'],
            '--primary-500' => $settings['primary'],
            '--st-header-from' => $settings['header_from'],
            '--st-header-to' => $settings['header_to'],
            '--st-surface' => $settings['surface'],
            '--st-rail' => $settings['rail'],
        ];
    }
}
