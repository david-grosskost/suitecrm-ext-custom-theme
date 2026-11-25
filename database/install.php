<?php

// Install script of custom-theme, run once at the install of the extension,
// in SuiteCRM, as the administrator, from public/legacy. It does two things:
//
// - lifts the cap on how many modules the menu shows, so the rail holds them
//   all with their submenus;
// - builds the stylesheet of the legacy theme from SuiteCRM's own and puts
//   the instance on it, so the screens SuiteCRM still draws — the dashboard,
//   the quote and invoice records, Studio, the administration — take the
//   client's colours too.
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

$written = LegacyTheme::write(ConfigTable::forSuiteCrm()->read(), '', sugar_cached(LegacyTheme::TARGET));

$configurator = new Configurator();
$configurator->loadConfig();
$configurator->config = Navigation::applyTo($configurator->config);
// The instance goes on the theme this extension ships. Its icons are
// suite8's, kept by config/services/theme-image-paths.yaml; without that
// file, changing the theme's name would hide 1204 of them.
$configurator->config['default_theme'] = LegacyTheme::NAME;
$configurator->handleOverride();

return 'custom-theme : tous les modules tiennent dans le menu (plafond levé). ' . $written;
