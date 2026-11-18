<?php

// Thème vertical (extension custom-theme): the entry point that serves the
// colours of the theme, open without login (auth false) so the interface is
// already themed on the login screen. It serves only the colours chosen in
// Administration and the accents of SuiteCRM's standard modules.

/** @var array<string, array{file: string, auth: bool}> $entry_point_registry Set by SuiteCRM */
$entry_point_registry['customTheme'] = [
    'file' => 'custom/include/custom-theme/ServeTheme.php',
    'auth' => false,
];

// The screen where an administrator chooses those colours. Behind the login
// (auth true), and the page itself turns away anyone who is not an
// administrator.
$entry_point_registry['customThemeAdmin'] = [
    'file' => 'custom/include/custom-theme/AdminPage.php',
    'auth' => true,
];
