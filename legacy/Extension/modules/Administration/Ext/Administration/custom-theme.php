<?php

// Thème vertical (extension custom-theme): the entry in Administration that
// leads to the colours of the theme.
//
// SuiteCRM joins every file of this folder into one, so the list of options
// starts empty here: otherwise this group would also show the entries of the
// extensions declared before it.

$admin_option_defs = [
    'Administration' => [
        'custom_theme_colours' => [
            'Administration',
            'LBL_CUSTOM_THEME_COLOURS',
            'LBL_CUSTOM_THEME_COLOURS_DESC',
            'index.php?entryPoint=customThemeAdmin',
        ],
    ],
];

/** @var array<int, array<int, mixed>> $admin_group_header Set by SuiteCRM */
$admin_group_header[] = [
    'LBL_CUSTOM_THEME_GROUP',
    '',
    false,
    $admin_option_defs,
    '',
];
