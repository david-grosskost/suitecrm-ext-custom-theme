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

// The screen where an administrator chooses those colours is not here: it is
// an action of the Administration module (ActionFileMap), because a link of
// the administration page must carry a module for SuiteCRM 8's route
// converter.
