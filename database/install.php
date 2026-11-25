<?php

// Install script of custom-theme: lifts the cap on how many modules the menu
// shows, so the rail holds them all with their submenus. Run once, at the
// install of the extension, in SuiteCRM, as the administrator, from
// public/legacy.
//
// It writes no file of its own, and could not: the scanner of the Module
// Loader refuses every filesystem call in a package, SuiteCRM's own wrappers
// included. The colours of the legacy screens are laid over them in the
// browser instead (LegacyTheme, and the Angular module).
//
// Its message is returned, not printed (echo is refused by the scanner of
// the Module Loader): the caller shows it.

declare(strict_types=1);

use SuiteCrmExt\CustomTheme\Navigation;

require_once __DIR__ . '/../legacy/include/custom-theme/Navigation.php';
// SuiteCRM's own configuration writer: the current directory is public/legacy.
require_once getcwd() . '/modules/Configurator/Configurator.php';

$configurator = new Configurator();
$configurator->loadConfig();
$configurator->config = Navigation::applyTo($configurator->config);
$configurator->handleOverride();

return 'custom-theme : tous les modules tiennent dans le menu (plafond levé).';
