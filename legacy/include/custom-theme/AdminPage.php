<?php

declare(strict_types=1);

// Vertical theme (extension custom-theme): the screen where the colours are
// chosen. Action CustomTheme of the Administration module, mapped to this
// file by legacy/Extension/modules/Administration/Ext/ActionFileMap/
// custom-theme.php. Run by SuiteCRM's controller, with the Administration
// labels in $mod_strings; administrators only.
//
// An action of the Administration module, not an entry point: a link in the
// administration page goes through SuiteCRM 8's route converter, which
// throws "No module defined" on a URL that carries no module. That exception
// takes down the whole navigation, not just this entry.
//
// It writes nothing but five colours, in the config table, in the category of
// this extension. Nothing of SuiteCRM is touched.

use SuiteCrmExt\CustomTheme\AdminForm;
use SuiteCrmExt\CustomTheme\ConfigTable;
use SuiteCrmExt\CustomTheme\LegacyTheme;
use SuiteCrmExt\CustomTheme\Page;
use SuiteCrmExt\CustomTheme\Palette;
use SuiteCrmExt\CustomTheme\StaleBrowsers;
use SuiteCrmExt\CustomTheme\PageView;

require_once __DIR__ . '/Palette.php';
require_once __DIR__ . '/Settings.php';
require_once __DIR__ . '/ConfigTable.php';
require_once __DIR__ . '/AdminForm.php';
require_once __DIR__ . '/LegacyTheme.php';
require_once __DIR__ . '/StaleBrowsers.php';
require_once getcwd() . '/modules/Configurator/Configurator.php';
require_once __DIR__ . '/Page.php';
// SuiteCRM's own view first (the current directory is public/legacy), then ours.
require_once getcwd() . '/include/MVC/View/SugarView.php';
require_once __DIR__ . '/PageView.php';

/** @var mixed $user */
$user = $GLOBALS['current_user'] ?? null;
if (!$user instanceof User || !is_admin($user)) {
    $denied = translate('ERR_NOT_ADMIN');
    sugar_die(is_string($denied) ? $denied : 'Unauthorized access to administration.');
}

/** @var mixed $labels */
$labels = $GLOBALS['mod_strings'] ?? [];
$labels = is_array($labels) ? $labels : [];

$settings = ConfigTable::forSuiteCrm();
$saved = false;

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if (is_string($method) && strtoupper($method) === 'POST') {
    $settings->replace(AdminForm::fromPost($_POST));
    $saved = true;
}

// The legacy screens are an iframe, which the variables of the page around
// it never reach: their stylesheet carries its own copy of the colours and
// is written again here. Also when it is older than SuiteCRM's own, which is
// what an upgrade leaves behind — opening this screen then repairs it.
if ($saved || LegacyTheme::isStale()) {
    LegacyTheme::write($settings->read());
    // Its address would not have moved, and browsers would keep the colours
    // they already have.
    $configurator = new Configurator();
    $configurator->loadConfig();
    $configurator->config = StaleBrowsers::applyTo($configurator->config);
    $configurator->handleOverride();
}

$view = new PageView(
    Page::label($labels, 'LBL_CT_ADMIN_TITLE'),
    AdminForm::body(Palette::settings($settings->read()), Page::URL, $saved),
);
$view->init();
$view->process();
