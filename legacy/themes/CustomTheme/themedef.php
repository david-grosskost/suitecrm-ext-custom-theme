<?php

// The legacy theme of this extension: a child of suite8, so that the screens
// SuiteCRM still draws itself take the client's colours. Copied to
// custom/themes/CustomTheme/, where SugarThemeRegistry reads it.
//
// No config_options here: a child inherits its parent's, and suite8 declares
// the sub-themes with Dawn as the default. Declaring a partial one would be
// worse than declaring none, the merge being shallow: it would replace the
// parent's block instead of completing it.
//
// Its stylesheet is not shipped beside this file. LegacyTheme builds it in
// the instance from SuiteCRM's own, at installation and whenever the colours
// change, because a child theme does not inherit its parent's sub-theme CSS
// and the whole stylesheet is therefore needed.

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$themedef = [
    'name' => 'Thème du client',
    'description' => "L'interface historique aux couleurs du client.",
    'parentTheme' => 'suite8',
    'version' => [
        'regex_matches' => ['.+'],
    ],
    'group_tabs' => true,
    'classic' => true,
    'configurable' => true,
];
