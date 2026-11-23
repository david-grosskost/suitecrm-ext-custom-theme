<?php

// Vertical theme (extension custom-theme): its section on the Administration
// page, with a link to the screen where the colours are chosen.
//
// Two rules this file must keep, both learned the hard way.
//
// The link must carry a module. Every link here is handed to SuiteCRM 8's
// route converter (RouteConverterHandler::convert), which throws
// "No module defined" on a URL without one. Nothing catches it: the
// appMetadata query fails whole, and the module menu comes back empty, with
// no trace in any log. An entry point URL is therefore out of the question,
// which is why the screen is an action of the Administration module.
//
// And this file holds arrays and nothing else. SuiteCRM assembles it into
// modules/Administration/metadata/adminpaneldefs.php, which two handlers load
// with a bare require in the same request: NavbarHandler for the module menu
// and AdminPanelDefinitionHandler for the administration page. A require, not
// a require_once: anything that cannot be declared twice (a function, a
// class, a constant) would raise a fatal error on the second pass.

/** @var array<array-key, array<int, mixed>> $admin_group_header Set by SuiteCRM */
$admin_option_defs = [
    'Administration' => [
        'custom_theme' => [
            'icon_AdminThemes',
            'LBL_CT_ADMIN_TITLE',
            'LBL_CT_ADMIN_DESC',
            './index.php?module=Administration&action=CustomTheme',
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
