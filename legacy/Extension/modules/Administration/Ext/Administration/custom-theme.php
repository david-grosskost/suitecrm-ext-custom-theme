<?php

// Vertical theme (extension custom-theme): its section on the Administration
// page, with a link to the screen where the colours are chosen.
//
// SuiteCRM assembles this file into modules/Administration/metadata/
// adminpaneldefs.php, which two handlers load with a bare require, in the
// same request: NavbarHandler (the module menu) and
// AdminPanelDefinitionHandler (the administration page). A require, not a
// require_once: anything declared here that cannot be declared twice (a
// function, a class, a constant) raises a fatal error on the second pass,
// and that error empties the whole module menu, not just this entry.
// So this file holds arrays and nothing else.

/** @var array<array-key, array<int, mixed>> $admin_group_header Set by SuiteCRM */
$admin_option_defs = [
    'Administration' => [
        'custom_theme' => [
            'icon_AdminThemes',
            'LBL_CT_ADMIN_TITLE',
            'LBL_CT_ADMIN_DESC',
            './index.php?entryPoint=customThemeAdmin',
            'admin-themes',
        ],
    ],
];
$admin_group_header[] = [
    'LBL_CT_ADMIN_HEADER',
    '',
    false,
    $admin_option_defs,
    'LBL_CT_ADMIN_HEADER_DESC',
    'module_settings',
];
