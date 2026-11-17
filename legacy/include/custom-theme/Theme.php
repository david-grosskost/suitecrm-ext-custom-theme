<?php

declare(strict_types=1);

namespace SuiteCrmExt\CustomTheme;

/**
 * Ce que l'interface reçoit pour se mettre aux couleurs : les variables CSS
 * à poser, et l'accent de chaque module pour le rail.
 *
 * Les accents sont calculés ici plutôt que dans le navigateur, pour que la
 * règle n'existe qu'à un seul endroit.
 *
 * Sur les modules **standard** de SuiteCRM seulement : l'entrée qui sert ces
 * couleurs est ouverte sans connexion, pour que le thème s'applique aussi à
 * l'écran de connexion, et elle ne doit donc rien dire de cette instance —
 * ni ses modules maison, ni leur nom. Un module absent de cette liste reçoit
 * côté navigateur une pastille portant son initiale.
 */
final class Theme
{
    /** Largeur du rail replié, en pixels. */
    public const int RAIL_WIDTH = 56;

    /** Largeur du rail déplié, quand on l'ouvre. */
    public const int RAIL_OPEN_WIDTH = 240;

    /**
     * Les modules livrés avec SuiteCRM 8, ceux dont l'icône existe dans
     * themes/suite8/images/<Module>.svg.
     *
     * @var list<string>
     */
    public const array STANDARD_MODULES = [
        'Accounts', 'Contacts', 'Opportunities', 'Leads', 'Prospects', 'ProspectLists',
        'Cases', 'Bugs', 'Calls', 'Meetings', 'Tasks', 'Notes', 'Emails', 'Calendar',
        'Documents', 'Campaigns', 'Project', 'ProjectTask', 'Targets', 'Activities',
        'AOS_Quotes', 'AOS_Invoices', 'AOS_Contracts', 'AOS_Products', 'AOS_PDF_Templates',
        'AOR_Reports', 'AOW_WorkFlow', 'AOK_KnowledgeBase', 'AM_ProjectTemplates',
        'FP_events', 'Surveys', 'SecurityGroups', 'Employees', 'Users', 'Home',
    ];

    /**
     * @param array<string, mixed> $stored ce que l'administrateur a choisi
     *
     * @return array{variables: array<string, string>, accents: array<string, string>, rail: array{width: int, open: int}}
     */
    public static function payload(array $stored): array
    {
        $accents = [];
        foreach (self::STANDARD_MODULES as $module) {
            $accents[$module] = Palette::accentFor($module);
        }

        return [
            'variables' => Palette::variables($stored),
            'accents' => $accents,
            'rail' => ['width' => self::RAIL_WIDTH, 'open' => self::RAIL_OPEN_WIDTH],
        ];
    }

    /** @param array<string, mixed> $stored */
    public static function json(array $stored): string
    {
        return json_encode(self::payload($stored), JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
    }
}
