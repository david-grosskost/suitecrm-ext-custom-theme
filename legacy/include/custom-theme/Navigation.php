<?php

declare(strict_types=1);

namespace SuiteCrmExt\CustomTheme;

/**
 * Combien de modules le menu de SuiteCRM accepte d'afficher.
 *
 * SuiteCRM en replie une partie dans un menu « Options » parce qu'une barre
 * horizontale manque de largeur : son plafond d'origine est de huit. Le rail
 * est vertical et défile, donc rien ne justifie de replier — et un module
 * replié perd son sous-menu, que SuiteCRM ne construit pas pour lui.
 *
 * Le plafond de la partie SuiteCRM 8 est levé ailleurs, dans
 * config/services/navigation-tab-limits.yaml : les deux vont ensemble.
 */
final class Navigation
{
    /** Au-delà du nombre de modules qu'une instance affiche en pratique. */
    public const int TABS = 60;

    /**
     * La configuration de l'instance, une fois le plafond levé.
     *
     * @param array<string, mixed> $config
     *
     * @return array<string, mixed>
     */
    public static function applyTo(array $config): array
    {
        $applied = $config;
        $applied['default_max_tabs'] = self::TABS;

        return $applied;
    }
}
