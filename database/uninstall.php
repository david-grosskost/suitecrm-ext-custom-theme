<?php

// Uninstall script of custom-theme, run before the files are removed, in
// SuiteCRM, as the administrator, from public/legacy.
//
// It puts the instance back on suite8 and takes away the stylesheet the
// install built, whose directory is about to disappear.
//
// A user who picked the theme in their own profile is left alone:
// SugarThemeRegistry::set refuses a theme that no longer exists and falls
// back by itself, and their preferences are stored all together in one
// serialized value — clearing the theme there would mean clearing their
// language, their date format and the rest with it.
//
// The cap on the menu is left as it is too: it holds no reference to this
// extension, an administrator may have set it on purpose, and putting back a
// value nobody asked for is worse than leaving a larger menu.
//
// Its message is returned, not printed (echo is refused by the scanner of
// the Module Loader): the caller shows it.

declare(strict_types=1);

use SuiteCrmExt\CustomTheme\LegacyTheme;

require_once __DIR__ . '/../legacy/include/custom-theme/Palette.php';
require_once __DIR__ . '/../legacy/include/custom-theme/LegacyTheme.php';
require_once getcwd() . '/modules/Configurator/Configurator.php';

$configurator = new Configurator();
$configurator->loadConfig();
if (($configurator->config['default_theme'] ?? '') === LegacyTheme::NAME) {
    $configurator->config['default_theme'] = 'suite8';
    $configurator->handleOverride();
}

foreach ([LegacyTheme::TARGET, sugar_cached(LegacyTheme::TARGET)] as $file) {
    if (is_file($file)) {
        unlink($file);
    }
}

return "custom-theme : l'instance est revenue au thème Suite 8.";
