<?php

// Thème vertical (extension custom-theme): the entry in Administration that
// leads to the colours of the theme.
//
// SuiteCRM joins every file of this folder into one, so $admin_option_defs is
// shared by every extension that declares an entry here. This file never
// assigns it: assigning wipes what the files before it put there, and
// SuiteCRM 8 builds its own menu from that structure — the whole module menu
// comes back empty. The group is described inline instead.

/** @var array<int, array<int, mixed>> $admin_group_header Set by SuiteCRM */
$admin_group_header[] = [
    'LBL_CUSTOM_THEME_GROUP',
    '',
    false,
    [
        'Administration' => [
            'custom_theme_colours' => [
                'Administration',
                'LBL_CUSTOM_THEME_COLOURS',
                'LBL_CUSTOM_THEME_COLOURS_DESC',
                'index.php?entryPoint=customThemeAdmin',
            ],
        ],
    ],
    '',
];
