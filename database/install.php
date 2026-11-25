<?php

// Install script of custom-theme, run once at the install of the extension,
// in SuiteCRM, as the administrator, from public/legacy. It does two things:
//
// - lifts the cap on how many modules the menu shows, so the rail holds them
//   all with their submenus;
// - lays the client's colours over the stylesheet of the legacy theme, so the
//   screens SuiteCRM still draws — the dashboard, Studio, the administration
//   — take them too.
//
// The theme itself is left alone, deliberately. On any theme other than
// suite8, SuiteCRM 8 serves its old record views instead of the modern ones:
// the colours are laid over suite8 rather than replacing it.
//
// Its message is returned, not printed (echo is refused by the scanner of
// the Module Loader): the caller shows it.

declare(strict_types=1);

use SuiteCrmExt\CustomTheme\ConfigTable;
use SuiteCrmExt\CustomTheme\LegacyTheme;
use SuiteCrmExt\CustomTheme\Navigation;

require_once __DIR__ . '/../legacy/include/custom-theme/Navigation.php';
require_once __DIR__ . '/../legacy/include/custom-theme/Palette.php';
require_once __DIR__ . '/../legacy/include/custom-theme/Settings.php';
require_once __DIR__ . '/../legacy/include/custom-theme/ConfigTable.php';
require_once __DIR__ . '/../legacy/include/custom-theme/LegacyTheme.php';
// SuiteCRM's own configuration writer: the current directory is public/legacy.
require_once getcwd() . '/modules/Configurator/Configurator.php';

$written = LegacyTheme::write(ConfigTable::forSuiteCrm()->read());

$configurator = new Configurator();
$configurator->loadConfig();
$configurator->config = Navigation::applyTo($configurator->config);
$configurator->handleOverride();

return 'custom-theme : tous les modules tiennent dans le menu (plafond levé). ' . $written;
